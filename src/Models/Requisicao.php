<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Requisicao
{
    public function getDepartamentos(): array
    {
        return Database::getConnection()->query("SELECT id, nome FROM departamentos WHERE ativo = TRUE ORDER BY nome")->fetchAll();
    }

    // Busca os almoxarifados que o departamento tem acesso
    public function getAlmoxarifadosPorDepartamento(int $departamentoId): array
    {
        $sql = "SELECT a.id, a.nome 
                  FROM almoxarifados a
                  JOIN departamentos_almoxarifados da ON da.almoxarifado_id = a.id
                 WHERE da.departamento_id = :dep_id AND a.ativo = TRUE";
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute(['dep_id' => $departamentoId]);
        return $stmt->fetchAll();
    }

    public function getProdutos(int $almoxarifadoId): array
    {
        // O JOIN com saldo_estoque garante que traremos a coluna 'quantidade'
        $sql = "SELECT p.id, p.codigo, p.descricao, s.quantidade 
                  FROM produtos p
                  JOIN saldo_estoque s ON s.produto_id = p.id
                 WHERE p.ativo = TRUE 
                   AND s.almoxarifado_id = :almox 
                   AND s.quantidade > 0
                 ORDER BY p.descricao";
                 
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute(['almox' => $almoxarifadoId]);
        return $stmt->fetchAll();
    }

    public function create(array $data, int $usuarioId): int
    {
        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            // Gera um número amigável para a requisição (Ex: REQ-0001)
            $stmtCount = $db->query("SELECT COUNT(*) + 1 FROM requisicoes");
            $proximoId = $stmtCount->fetchColumn();
            $numero = "REQ-" . str_pad($proximoId, 4, '0', STR_PAD_LEFT);

            $sqlReq = "INSERT INTO requisicoes (numero, solicitante_id, departamento_id, almoxarifado_id, justificativa, prioridade, status) 
                       VALUES (:numero, :sol, :dep, :almox, :just, :prio, 'RASCUNHO') RETURNING id";
            
            $stmtReq = $db->prepare($sqlReq);
            $stmtReq->execute([
                'numero' => $numero,
                'sol'    => $usuarioId,
                'dep'    => $data['departamento_id'],
                'almox'  => $data['almoxarifado_id'],
                'just'   => $data['justificativa'],
                'prio'   => $data['prioridade']
            ]);
            $requisicaoId = $stmtReq->fetchColumn();

            // Insere os itens (que vieram como arrays do formulário)
            $sqlItem = "INSERT INTO requisicoes_itens (requisicao_id, produto_id, quantidade_solicitada) 
                        VALUES (:req, :prod, :qtd)";
            $stmtItem = $db->prepare($sqlItem);

            foreach ($data['produto_id'] as $index => $produtoId) {
                $qtd = $data['quantidade'][$index];
                if ($produtoId && $qtd > 0) {
                    $stmtItem->execute([
                        'req'  => $requisicaoId,
                        'prod' => $produtoId,
                        'qtd'  => $qtd
                    ]);
                }
            }

            $db->commit();
            return $requisicaoId;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function listarRecentes(int $usuarioId, string $perfil, array $filtros = []): array
    {
        $sql = "SELECT r.id, r.numero, r.status, r.prioridade, r.criado_em, 
                       d.nome AS departamento, u.nome AS solicitante
                  FROM requisicoes r
                  JOIN departamentos d ON d.id = r.departamento_id
                  JOIN usuarios u ON u.id = r.solicitante_id
                 WHERE 1=1"; // 1=1 facilita a concatenação de ANDs
                  
        $params = [];

        if ($perfil === 'OPERADOR') {
            $sql .= " AND r.solicitante_id = :usuario";
            $params['usuario'] = $usuarioId;
        }

        // Filtro por Status
        if (!empty($filtros['status'])) {
            $sql .= " AND r.status = :status";
            $params['status'] = $filtros['status'];
        }

        // Filtro por Número da Requisição
        if (!empty($filtros['numero'])) {
            $sql .= " AND r.numero ILIKE :numero";
            $params['numero'] = '%' . $filtros['numero'] . '%';
        }
        
        // Filtro por Departamento
        if (!empty($filtros['departamento_id'])) {
            $sql .= " AND r.departamento_id = :dep_id";
            $params['dep_id'] = $filtros['departamento_id'];
        }
        
        $sql .= " ORDER BY r.id DESC LIMIT 100";
        
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Busca os dados da requisição e os seus itens
    public function find(int $id): ?array
    {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("
            SELECT r.*, d.nome AS departamento, u.nome AS solicitante, a.nome AS almoxarifado,
                   ap.nome AS aprovador
              FROM requisicoes r
              JOIN departamentos d ON d.id = r.departamento_id
              JOIN usuarios u ON u.id = r.solicitante_id
              JOIN almoxarifados a ON a.id = r.almoxarifado_id
              LEFT JOIN usuarios ap ON ap.id = r.aprovador_id
             WHERE r.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $req = $stmt->fetch();
        
        if (!$req) return null;

        // Busca os itens
        $stmtItens = $db->prepare("
            SELECT ri.*, p.codigo, p.descricao, p.unidade_medida_id, u.sigla AS unidade
              FROM requisicoes_itens ri
              JOIN produtos p ON p.id = ri.produto_id
              JOIN unidades_medida u ON u.id = p.unidade_medida_id
             WHERE ri.requisicao_id = :id
        ");
        $stmtItens->execute(['id' => $id]);
        $req['itens'] = $stmtItens->fetchAll();

        return $req;
    }

    // Envia para aprovação (RASCUNHO -> AGUARDANDO_APROVACAO)
    public function enviarParaAprovacao(int $id): bool
    {
        $stmt = Database::getConnection()->prepare("UPDATE requisicoes SET status = 'AGUARDANDO_APROVACAO' WHERE id = :id AND status = 'RASCUNHO'");
        return $stmt->execute(['id' => $id]);
    }

        // Aprova a requisição (AGUARDANDO_APROVACAO -> APROVADA)
    public function aprovar(int $id, int $aprovadorId): bool
    {
        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            // 1. Atualiza o status no cabeçalho da requisição
            $sql = "UPDATE requisicoes 
                       SET status = 'APROVADA', aprovador_id = :aprovador, data_aprovacao = NOW()
                     WHERE id = :id AND status = 'AGUARDANDO_APROVACAO'";
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id, 'aprovador' => $aprovadorId]);

            // 2. Copia a quantidade solicitada para a quantidade aprovada NOS ITENS
            $sqlItens = "UPDATE requisicoes_itens 
                            SET quantidade_aprovada = quantidade_solicitada 
                          WHERE requisicao_id = :id";
            $stmtItens = $db->prepare($sqlItens);
            $stmtItens->execute(['id' => $id]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    // Rejeita a requisição (AGUARDANDO_APROVACAO -> REJEITADA)
    public function rejeitar(int $id, int $aprovadorId, string $motivo): bool
    {
        $sql = "UPDATE requisicoes 
                   SET status = 'REJEITADA', aprovador_id = :aprovador, data_aprovacao = NOW(), motivo_rejeicao = :motivo 
                 WHERE id = :id AND status = 'AGUARDANDO_APROVACAO'";
        $stmt = Database::getConnection()->prepare($sql);
        return $stmt->execute(['id' => $id, 'aprovador' => $aprovadorId, 'motivo' => $motivo]);
    }

    // Função para atender a requisição (APROVADA -> ATENDIDA)
    public function atender(int $id, array $quantidadesAtendidas, int $usuarioId): bool
    {
        $db = Database::getConnection();
        $req = $this->find($id);

        if (!$req || !in_array($req['status'], ['APROVADA', 'ATENDIDA_PARCIAL'])) {
            throw new \Exception("Esta requisição não está pronta para atendimento.");
        }

        try {
            $db->beginTransaction();
            $temPendencia = false;

            // 1. Criar o cabeçalho da movimentação (só se houver algo para entregar)
            $sqlMov = "INSERT INTO movimentacoes (tipo, subtipo, usuario_id, almoxarifado_origem_id, departamento_id, requisicao_id, observacao) 
                       VALUES ('SAIDA', 'REQUISICAO', :usuario, :almox, :dep, :req_id, :obs) RETURNING id";
            $stmtMov = $db->prepare($sqlMov);
            $stmtMov->execute([
                'usuario' => $usuarioId, 'almox' => $req['almoxarifado_id'], 'dep' => $req['departamento_id'],
                'req_id' => $req['id'], 'obs' => 'Atendimento da ' . $req['numero']
            ]);
            $movimentacaoId = $stmtMov->fetchColumn();

            $sqlItemMov = "INSERT INTO itens_movimentacao (movimentacao_id, produto_id, quantidade) VALUES (:mov, :prod, :qtd)";
            $stmtItemMov = $db->prepare($sqlItemMov);

            $sqlUpdateReqItem = "UPDATE requisicoes_itens SET quantidade_atendida = quantidade_atendida + :qtd WHERE id = :id";
            $stmtUpdateReqItem = $db->prepare($sqlUpdateReqItem);

            // 2. Processar item por item
            foreach ($req['itens'] as $item) {
                $entregarAgora = (float) ($quantidadesAtendidas[$item['id']] ?? 0);
                $pendente = (float) $item['quantidade_aprovada'] - (float) $item['quantidade_atendida'];

                // Se o usuário tentou entregar mais do que deve, limitamos ao pendente
                if ($entregarAgora > $pendente) {
                    $entregarAgora = $pendente;
                }

                if ($entregarAgora > 0) {
                    // Verifica saldo real antes de baixar
                    $stmtSaldo = $db->prepare("SELECT COALESCE(SUM(quantidade), 0) FROM saldo_estoque WHERE produto_id = :p AND almoxarifado_id = :a");
                    $stmtSaldo->execute(['p' => $item['produto_id'], 'a' => $req['almoxarifado_id']]);
                    $saldoAtual = (float) $stmtSaldo->fetchColumn();

                    if ($saldoAtual < $entregarAgora) {
                        throw new \Exception("Saldo insuficiente para {$item['descricao']}. Disponível: {$saldoAtual}");
                    }

                    // Baixa no estoque
                    $stmtItemMov->execute(['mov' => $movimentacaoId, 'prod' => $item['produto_id'], 'qtd' => $entregarAgora]);
                    // Atualiza o item da requisição
                    $stmtUpdateReqItem->execute(['qtd' => $entregarAgora, 'id' => $item['id']]);
                }

                // Verifica se ainda vai sobrar algo pendente
                if (($item['quantidade_atendida'] + $entregarAgora) < $item['quantidade_aprovada']) {
                    $temPendencia = true;
                }
            }

            // 3. Define o status final
            $novoStatus = $temPendencia ? 'ATENDIDA_PARCIAL' : 'ATENDIDA';
            $db->prepare("UPDATE requisicoes SET status = :status WHERE id = :id")->execute(['status' => $novoStatus, 'id' => $id]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function listarBackorders(): array
    {
        $sql = "SELECT r.id AS requisicao_id, r.numero, r.status, r.criado_em,
                       d.nome AS departamento,
                       p.codigo, p.descricao, p.id AS produto_id,
                       ri.id AS item_id,
                       ri.quantidade_aprovada, ri.quantidade_atendida,
                       (ri.quantidade_aprovada - ri.quantidade_atendida) AS quantidade_pendente,
                       COALESCE(SUM(s.quantidade), 0) AS estoque_atual
                  FROM requisicoes_itens ri
                  JOIN requisicoes r ON r.id = ri.requisicao_id
                  JOIN produtos p ON p.id = ri.produto_id
                  JOIN departamentos d ON d.id = r.departamento_id
                  LEFT JOIN saldo_estoque s ON s.produto_id = ri.produto_id AND s.almoxarifado_id = r.almoxarifado_id
                 WHERE r.status IN ('ATENDIDA_PARCIAL', 'AGUARDANDO_ESTOQUE')
                   AND ri.quantidade_atendida < ri.quantidade_aprovada
                 GROUP BY r.id, d.nome, p.id, ri.id
                 ORDER BY r.criado_em ASC"; // Mais antigas primeiro (FIFO)
                 
        return Database::getConnection()->query($sql)->fetchAll();
    }

    public function cancelar(int $id, string $motivo, int $usuarioId): bool
    {
        $db = Database::getConnection();
        $req = $this->find($id);

        // Não pode cancelar o que já foi totalmente atendido ou já está cancelado
        if (!$req || in_array($req['status'], ['ATENDIDA', 'CANCELADA'])) {
            throw new \Exception("Esta requisição não pode ser cancelada.");
        }

        try {
            $db->beginTransaction();

            // 1. Verificar se houve algum atendimento parcial (precisa devolver ao estoque)
            $temItensAtendidos = false;
            $itensParaDevolver = [];

            foreach ($req['itens'] as $item) {
                if ((float) $item['quantidade_atendida'] > 0) {
                    $temItensAtendidos = true;
                    $itensParaDevolver[] = $item;
                }
            }

            // 2. Se teve entrega, gera uma movimentação de ENTRADA (Devolução)
            if ($temItensAtendidos) {
                $sqlMov = "INSERT INTO movimentacoes (tipo, subtipo, usuario_id, almoxarifado_destino_id, requisicao_id, observacao) 
                           VALUES ('ENTRADA', 'DEVOLUCAO', :usuario, :almox, :req_id, :obs) RETURNING id";
                $stmtMov = $db->prepare($sqlMov);
                $stmtMov->execute([
                    'usuario' => $usuarioId,
                    'almox'   => $req['almoxarifado_id'],
                    'req_id'  => $req['id'],
                    'obs'     => 'Estorno por cancelamento da ' . $req['numero']
                ]);
                $movimentacaoId = $stmtMov->fetchColumn();

                $sqlItemMov = "INSERT INTO itens_movimentacao (movimentacao_id, produto_id, quantidade) VALUES (:mov, :prod, :qtd)";
                $stmtItemMov = $db->prepare($sqlItemMov);

                foreach ($itensParaDevolver as $item) {
                    // Devolve o que já havia sido entregue
                    $stmtItemMov->execute([
                        'mov'  => $movimentacaoId,
                        'prod' => $item['produto_id'],
                        'qtd'  => $item['quantidade_atendida']
                    ]);
                }
            }

            // 3. Atualiza o status da requisição para CANCELADA
            $sqlReq = "UPDATE requisicoes 
                          SET status = 'CANCELADA', motivo_cancelamento = :motivo 
                        WHERE id = :id";
            $db->prepare($sqlReq)->execute(['motivo' => $motivo, 'id' => $id]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

}   