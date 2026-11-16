<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class PedidoCompra
{
    public function create(array $data, int $usuarioId): int
    {
        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            // Gera número PC-0001
            $stmtCount = $db->query("SELECT COUNT(*) + 1 FROM pedidos_compra");
            $proximoId = $stmtCount->fetchColumn();
            $numero = "PC-" . str_pad($proximoId, 4, '0', STR_PAD_LEFT);

            $sqlPedido = "INSERT INTO pedidos_compra (numero, solicitante_id, justificativa, status) 
                          VALUES (:numero, :sol, :just, 'RASCUNHO') RETURNING id";
            $stmtPedido = $db->prepare($sqlPedido);
            $stmtPedido->execute([
                'numero' => $numero,
                'sol' => $usuarioId,
                'just' => $data['justificativa']
            ]);
            $pedidoId = $stmtPedido->fetchColumn();

            $sqlItem = "INSERT INTO pedidos_compra_itens (pedido_id, produto_id, quantidade, valor_unitario) 
                        VALUES (:pedido, :prod, :qtd, :valor)";
            $stmtItem = $db->prepare($sqlItem);

            foreach ($data['produto_id'] as $index => $produtoId) {
                $qtd = str_replace(',', '.', $data['quantidade'][$index]);
                $valor = !empty($data['valor_unitario'][$index]) ? str_replace(',', '.', $data['valor_unitario'][$index]) : null;

                if ($produtoId && $qtd > 0) {
                    $stmtItem->execute([
                        'pedido' => $pedidoId,
                        'prod' => $produtoId,
                        'qtd' => $qtd,
                        'valor' => $valor
                    ]);
                }
            }

            $db->commit();
            return $pedidoId;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function find(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT p.*, u.nome AS solicitante, a.nome AS aprovador, 
                   f.razao_social AS fornecedor_nome, al.nome AS almoxarifado_nome
              FROM pedidos_compra p
              JOIN usuarios u ON u.id = p.solicitante_id
              LEFT JOIN usuarios a ON a.id = p.aprovador_id
              LEFT JOIN fornecedores f ON f.id = p.fornecedor_id
              LEFT JOIN almoxarifados al ON al.id = p.almoxarifado_id
             WHERE p.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $pedido = $stmt->fetch();

        if (!$pedido)
            return null;

        $stmtItens = $db->prepare("
            SELECT i.*, pr.codigo, pr.descricao, pr.controla_lote, un.sigla AS unidade
              FROM pedidos_compra_itens i
              JOIN produtos pr ON pr.id = i.produto_id
              JOIN unidades_medida un ON un.id = pr.unidade_medida_id
             WHERE i.pedido_id = :id
        ");
        $stmtItens->execute(['id' => $id]);
        $pedido['itens'] = $stmtItens->fetchAll();

        return $pedido;
    }

    public function mudarStatus(int $id, string $novoStatus, int $usuarioId = null, string $documento = null): bool
    {
        $sql = "UPDATE pedidos_compra SET status = :status";
        $params = ['status' => $novoStatus, 'id' => $id];

        if ($novoStatus === 'APROVADO' || $novoStatus === 'REJEITADO') {
            $sql .= ", aprovador_id = :aprovador, data_aprovacao = NOW()";
            $params['aprovador'] = $usuarioId;
        }

        if ($novoStatus === 'COMPRADO') {
            $sql .= ", data_compra = NOW(), documento_compra = :doc";
            $params['doc'] = $documento;
        }

        $sql .= " WHERE id = :id";
        $stmt = Database::getConnection()->prepare($sql);
        return $stmt->execute($params);
    }

    public function receber(int $pedidoId, array $dadosRecebimento, int $usuarioId): bool
    {
        $db = Database::getConnection();
        $pedido = $this->find($pedidoId);

        if (!$pedido || !in_array($pedido['status'], ['COMPRADO', 'RECEBIDO_PARCIAL'])) {
            throw new \Exception("Este pedido não está pronto para recebimento.");
        }

        try {
            $db->beginTransaction();

            // 1. Atualiza o cabeçalho do pedido com o almoxarifado e fornecedor
            $sqlUpdatePedido = "UPDATE pedidos_compra 
                                   SET almoxarifado_id = :almox, fornecedor_id = :forn 
                                 WHERE id = :id";
            $db->prepare($sqlUpdatePedido)->execute([
                'almox' => $dadosRecebimento['almoxarifado_id'],
                'forn' => !empty($dadosRecebimento['fornecedor_id']) ? $dadosRecebimento['fornecedor_id'] : null,
                'id' => $pedidoId
            ]);

            // 2. Cria a movimentação de ENTRADA
            $sqlMov = "INSERT INTO movimentacoes (tipo, subtipo, usuario_id, almoxarifado_destino_id, fornecedor_id, documento, observacao) 
                       VALUES ('ENTRADA', 'COMPRA', :usuario, :almox, :forn, :doc, :obs) RETURNING id";
            $stmtMov = $db->prepare($sqlMov);
            $stmtMov->execute([
                'usuario' => $usuarioId,
                'almox' => $dadosRecebimento['almoxarifado_id'],
                'forn' => !empty($dadosRecebimento['fornecedor_id']) ? $dadosRecebimento['fornecedor_id'] : null,
                'doc' => $pedido['documento_compra'],
                'obs' => 'Recebimento do Pedido de Compra ' . $pedido['numero']
            ]);
            $movimentacaoId = $stmtMov->fetchColumn();

            $sqlItemMov = "INSERT INTO itens_movimentacao (movimentacao_id, produto_id, lote_id, quantidade, valor_unitario) 
                           VALUES (:mov, :prod, :lote, :qtd, :valor)";
            $stmtItemMov = $db->prepare($sqlItemMov);

            $sqlUpdateItemPedido = "UPDATE pedidos_compra_itens SET quantidade_recebida = quantidade_recebida + :qtd WHERE id = :id";
            $stmtUpdateItemPedido = $db->prepare($sqlUpdateItemPedido);

            $recebeuTudo = true;

            // 3. Processa item por item
            foreach ($pedido['itens'] as $item) {
                $qtdRecebidaAgora = (float) ($dadosRecebimento['qtd_recebida'][$item['id']] ?? 0);
                $pendente = (float) $item['quantidade'] - (float) $item['quantidade_recebida'];

                if ($qtdRecebidaAgora > $pendente) {
                    $qtdRecebidaAgora = $pendente; // Não deixa receber mais do que pediu
                }

                if ($qtdRecebidaAgora > 0) {
                    $loteId = null;

                    // Se o produto controla lote, cria ou busca o lote
                    if ($item['controla_lote'] && !empty($dadosRecebimento['lote'][$item['id']])) {
                        $numeroLote = $dadosRecebimento['lote'][$item['id']];
                        $validade = $dadosRecebimento['validade'][$item['id']] ?? null;

                        $stmtLote = $db->prepare("SELECT id FROM lotes WHERE produto_id = :prod AND numero_lote = :num");
                        $stmtLote->execute(['prod' => $item['produto_id'], 'num' => $numeroLote]);
                        $loteId = $stmtLote->fetchColumn();

                        if (!$loteId) {
                            $stmtNovoLote = $db->prepare("INSERT INTO lotes (produto_id, numero_lote, validade) VALUES (:prod, :num, :val) RETURNING id");
                            $stmtNovoLote->execute(['prod' => $item['produto_id'], 'num' => $numeroLote, 'val' => $validade]);
                            $loteId = $stmtNovoLote->fetchColumn();
                        }
                    }

                    // Insere na movimentação (a Trigger do banco vai atualizar o saldo)
                    $stmtItemMov->execute([
                        'mov' => $movimentacaoId,
                        'prod' => $item['produto_id'],
                        'lote' => $loteId,
                        'qtd' => $qtdRecebidaAgora,
                        'valor' => !empty($item['valor_unitario']) ? $item['valor_unitario'] : null
                    ]);

                    // Atualiza o pedido de compra
                    $stmtUpdateItemPedido->execute(['qtd' => $qtdRecebidaAgora, 'id' => $item['id']]);
                }

                // Verifica se ainda vai sobrar algo pendente
                if (($item['quantidade_recebida'] + $qtdRecebidaAgora) < $item['quantidade']) {
                    $recebeuTudo = false;
                }
            }

            // 4. Define o status final
            $novoStatus = $recebeuTudo ? 'RECEBIDO' : 'RECEBIDO_PARCIAL';
            $db->prepare("UPDATE pedidos_compra SET status = :status WHERE id = :id")->execute(['status' => $novoStatus, 'id' => $pedidoId]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}