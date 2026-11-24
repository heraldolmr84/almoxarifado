<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Movimentacao
{
    public function getAlmoxarifados(): array
    {
        return Database::getConnection()->query("SELECT id, nome FROM almoxarifados WHERE ativo = TRUE")->fetchAll();
    }

    public function getProdutos(): array
    {
        return Database::getConnection()->query("SELECT id, codigo, descricao, controla_lote FROM produtos WHERE ativo = TRUE ORDER BY descricao")->fetchAll();
    }

    public function registrarEntrada(array $data, int $usuarioId): bool
    {
        $db = Database::getConnection();

        // LÓGICA DO FORNECEDOR:
        $fornecedorId = null;
        if (!empty($data['fornecedor_id'])) {
            $valor = trim($data['fornecedor_id']);

            if (is_numeric($valor)) {
                // Se for número, é um ID já existente
                $fornecedorId = (int) $valor;
            } else {
                // Se for texto, é um fornecedor novo. Usa a função que já criamos!
                $fornecedorId = (new Fornecedor())->findOrCreate($valor);
            }
        }

        try {
            $db->beginTransaction();

            // 1. Descobre se o produto controla lote
            $stmtProd = $db->prepare("SELECT controla_lote FROM produtos WHERE id = :id");
            $stmtProd->execute(['id' => $data['produto_id']]);
            $controlaLote = $stmtProd->fetchColumn();

            $loteId = null;

            // 2. Se controla lote, cria ou busca o lote existente
            if ($controlaLote && !empty($data['numero_lote'])) {
                $sqlLote = "SELECT id FROM lotes WHERE produto_id = :prod AND numero_lote = :num";
                $stmtLote = $db->prepare($sqlLote);
                $stmtLote->execute(['prod' => $data['produto_id'], 'num' => $data['numero_lote']]);
                $loteId = $stmtLote->fetchColumn();

                if (!$loteId) {
                    $sqlNovoLote = "INSERT INTO lotes (produto_id, numero_lote, validade) VALUES (:prod, :num, :val) RETURNING id";
                    $stmtNovoLote = $db->prepare($sqlNovoLote);
                    $stmtNovoLote->execute([
                        'prod' => $data['produto_id'],
                        'num' => $data['numero_lote'],
                        'val' => !empty($data['validade']) ? $data['validade'] : null
                    ]);
                    $loteId = $stmtNovoLote->fetchColumn();
                }
            }

            // 3. Insere o cabeçalho
            $sqlMov = "INSERT INTO movimentacoes (tipo, subtipo, usuario_id, almoxarifado_destino_id, fornecedor_id, documento, observacao) 
                       VALUES ('ENTRADA', 'COMPRA', :usuario, :almox, :fornecedor, :doc, :obs) RETURNING id";
            $stmtMov = $db->prepare($sqlMov);
            $stmtMov->execute([
                'usuario' => $usuarioId,
                'almox' => $data['almoxarifado_id'],
                'fornecedor' => $fornecedorId,
                'doc' => $data['documento'] ?? null,
                'obs' => $data['observacao'] ?? null
            ]);

            $movimentacaoId = $stmtMov->fetchColumn();

            // 4. Insere o item com o lote_id
            $sqlItem = "INSERT INTO itens_movimentacao (movimentacao_id, produto_id, lote_id, quantidade, valor_unitario) 
                        VALUES (:mov_id, :produto, :lote, :qtd, :valor)";
            $stmtItem = $db->prepare($sqlItem);
            $stmtItem->execute([
                'mov_id' => $movimentacaoId,
                'produto' => $data['produto_id'],
                'lote' => $loteId,
                'qtd' => $data['quantidade'],
                'valor' => !empty($data['valor_unitario']) ? $data['valor_unitario'] : null
            ]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function listarRecentes(array $filtros = [], array $almoxPermitidos = [], int $limit = 8, int $offset = 0): array
    {
        $sql = "SELECT m.id, m.data_movimentacao, m.tipo, m.subtipo, m.documento, 
                       u.nome AS usuario, 
                       COALESCE(ad.nome, ao.nome) AS almoxarifado
                  FROM movimentacoes m
                  JOIN usuarios u ON u.id = m.usuario_id
                  LEFT JOIN almoxarifados ad ON ad.id = m.almoxarifado_destino_id
                  LEFT JOIN almoxarifados ao ON ao.id = m.almoxarifado_origem_id
                 WHERE 1=1";

        $params = [];

        if (!empty($almoxPermitidos)) {
            $inQuery = implode(',', array_map('intval', $almoxPermitidos));
            $sql .= " AND (m.almoxarifado_origem_id IN ($inQuery) OR m.almoxarifado_destino_id IN ($inQuery))";
        }

        if (!empty($filtros['tipo'])) {
            $sql .= " AND m.tipo = :tipo";
            $params['tipo'] = $filtros['tipo'];
        }
        if (!empty($filtros['documento'])) {
            $sql .= " AND m.documento ILIKE :documento";
            $params['documento'] = '%' . $filtros['documento'] . '%';
        }
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND m.data_movimentacao >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'] . ' 00:00:00';
        }
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND m.data_movimentacao <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'] . ' 23:59:59';
        }

        $sql .= " ORDER BY m.id DESC LIMIT :limit OFFSET :offset";

        $stmt = Database::getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(array $filtros = [], array $almoxPermitidos = []): int
    {
        $sql = "SELECT COUNT(*) FROM movimentacoes m WHERE 1=1";
        $params = [];

        if (!empty($almoxPermitidos)) {
            $inQuery = implode(',', array_map('intval', $almoxPermitidos));
            $sql .= " AND (m.almoxarifado_origem_id IN ($inQuery) OR m.almoxarifado_destino_id IN ($inQuery))";
        }

        if (!empty($filtros['tipo'])) {
            $sql .= " AND m.tipo = :tipo";
            $params['tipo'] = $filtros['tipo'];
        }
        if (!empty($filtros['documento'])) {
            $sql .= " AND m.documento ILIKE :documento";
            $params['documento'] = '%' . $filtros['documento'] . '%';
        }
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND m.data_movimentacao >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'] . ' 00:00:00';
        }
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND m.data_movimentacao <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'] . ' 23:59:59';
        }

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    // Método auxiliar para consultar o saldo atual
    public function getSaldo(int $produtoId, int $almoxarifadoId): float
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT COALESCE(SUM(quantidade), 0) FROM saldo_estoque WHERE produto_id = :p AND almoxarifado_id = :a"
        );
        $stmt->execute(['p' => $produtoId, 'a' => $almoxarifadoId]);
        $saldo = $stmt->fetchColumn();
        return $saldo ? (float) $saldo : 0.0;
    }

    public function registrarSaida(array $data, int $usuarioId): bool
    {
        $db = Database::getConnection();

        // 1. Verifica se há saldo suficiente antes de tentar salvar
        $saldoAtual = $this->getSaldo($data['produto_id'], $data['almoxarifado_id']);

        if ($saldoAtual < $data['quantidade']) {
            throw new \Exception("Saldo insuficiente. O saldo atual deste produto neste almoxarifado é de {$saldoAtual}.");
        }

        try {
            $db->beginTransaction();

            // 2. Insere o cabeçalho da movimentação
            $sqlMov = "INSERT INTO movimentacoes (tipo, subtipo, usuario_id, almoxarifado_origem_id, documento, observacao) 
                       VALUES ('SAIDA', :subtipo, :usuario, :almox, :doc, :obs) RETURNING id";
            $stmtMov = $db->prepare($sqlMov);
            $stmtMov->execute([
                'subtipo' => $data['subtipo'] ?? 'CONSUMO',
                'usuario' => $usuarioId,
                'almox' => $data['almoxarifado_id'],
                'doc' => $data['documento'] ?? null,
                'obs' => $data['observacao'] ?? null
            ]);
            $movimentacaoId = $stmtMov->fetchColumn();

            // 3. Insere os itens (A trigger do banco vai subtrair do saldo_estoque)
            $sqlItem = "INSERT INTO itens_movimentacao (movimentacao_id, produto_id, lote_id, quantidade) 
                        VALUES (:mov_id, :produto, :lote, :qtd)";
            $stmtItem = $db->prepare($sqlItem);
            $stmtItem->execute([
                'mov_id' => $movimentacaoId,
                'produto' => $data['produto_id'],
                'lote' => !empty($data['lote_id']) ? $data['lote_id'] : null,
                'qtd' => $data['quantidade']
            ]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function find(int $id): ?array
    {
        $db = Database::getConnection();

        $sql = "SELECT m.*, 
                       u.nome AS usuario,
                       ad.nome AS almox_destino, 
                       ao.nome AS almox_origem,
                       d.nome AS departamento,
                       r.numero AS numero_requisicao
                  FROM movimentacoes m
                  JOIN usuarios u ON u.id = m.usuario_id
                  LEFT JOIN almoxarifados ad ON ad.id = m.almoxarifado_destino_id
                  LEFT JOIN almoxarifados ao ON ao.id = m.almoxarifado_origem_id
                  LEFT JOIN departamentos d ON d.id = m.departamento_id
                  LEFT JOIN requisicoes r ON r.id = m.requisicao_id
                 WHERE m.id = :id";

        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $mov = $stmt->fetch();

        if (!$mov)
            return null;

        // Busca os itens da movimentação
        $sqlItens = "SELECT i.*, p.codigo, p.descricao, u.sigla AS unidade,
                            l.numero_lote, l.validade
                       FROM itens_movimentacao i
                       JOIN produtos p ON p.id = i.produto_id
                       JOIN unidades_medida u ON u.id = p.unidade_medida_id
                       LEFT JOIN lotes l ON l.id = i.lote_id
                      WHERE i.movimentacao_id = :id";

        $stmtItens = $db->prepare($sqlItens);
        $stmtItens->execute(['id' => $id]);
        $mov['itens'] = $stmtItens->fetchAll();

        return $mov;
    }

    // Função para registrar transferência entre almoxarifados
    public function registrarTransferencia(array $data, int $usuarioId): bool
    {
        if ($data['almoxarifado_origem_id'] == $data['almoxarifado_destino_id']) {
            throw new \Exception("O almoxarifado de origem e destino não podem ser iguais.");
        }

        $db = Database::getConnection();
        $saldoAtual = $this->getSaldo($data['produto_id'], $data['almoxarifado_origem_id']);

        if ($saldoAtual < $data['quantidade']) {
            throw new \Exception("Saldo insuficiente na origem. Disponível: {$saldoAtual}");
        }

        try {
            $db->beginTransaction();

            // 1. MOVIMENTAÇÃO DE SAÍDA (Origem)
            $sqlSaida = "INSERT INTO movimentacoes (tipo, subtipo, usuario_id, almoxarifado_origem_id, observacao) 
                         VALUES ('SAIDA', 'TRANSFERENCIA', :usuario, :origem, :obs) RETURNING id";
            $stmtSaida = $db->prepare($sqlSaida);
            $stmtSaida->execute([
                'usuario' => $usuarioId,
                'origem' => $data['almoxarifado_origem_id'],
                'obs' => 'Transferência para outro almoxarifado'
            ]);
            $movSaidaId = $stmtSaida->fetchColumn();

            $db->prepare("INSERT INTO itens_movimentacao (movimentacao_id, produto_id, quantidade) VALUES (:mov, :prod, :qtd)")
                ->execute(['mov' => $movSaidaId, 'prod' => $data['produto_id'], 'qtd' => $data['quantidade']]);

            // 2. MOVIMENTAÇÃO DE ENTRADA (Destino)
            $sqlEntrada = "INSERT INTO movimentacoes (tipo, subtipo, usuario_id, almoxarifado_destino_id, observacao) 
                           VALUES ('ENTRADA', 'TRANSFERENCIA', :usuario, :destino, :obs) RETURNING id";
            $stmtEntrada = $db->prepare($sqlEntrada);
            $stmtEntrada->execute([
                'usuario' => $usuarioId,
                'destino' => $data['almoxarifado_destino_id'],
                'obs' => 'Recebimento via transferência'
            ]);
            $movEntradaId = $stmtEntrada->fetchColumn();

            $db->prepare("INSERT INTO itens_movimentacao (movimentacao_id, produto_id, quantidade) VALUES (:mov, :prod, :qtd)")
                ->execute(['mov' => $movEntradaId, 'prod' => $data['produto_id'], 'qtd' => $data['quantidade']]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function getLotesDisponiveis(int $produtoId, int $almoxarifadoId): array
    {
        $sql = "SELECT l.id, l.numero_lote, l.validade, s.quantidade 
                  FROM lotes l
                  JOIN saldo_estoque s ON s.lote_id = l.id
                 WHERE l.produto_id = :prod AND s.almoxarifado_id = :almox AND s.quantidade > 0
                 ORDER BY l.validade ASC NULLS LAST"; // FEFO: Vence primeiro, sai primeiro
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute(['prod' => $produtoId, 'almox' => $almoxarifadoId]);
        return $stmt->fetchAll();
    }
}