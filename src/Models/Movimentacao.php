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
        return Database::getConnection()->query("SELECT id, codigo, descricao FROM produtos WHERE ativo = TRUE ORDER BY descricao")->fetchAll();
    }

    public function registrarEntrada(array $data, int $usuarioId): bool
    {
        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            // 1. Insere o cabeçalho da movimentação
            $sqlMov = "INSERT INTO movimentacoes (tipo, subtipo, usuario_id, almoxarifado_destino_id, documento, observacao) 
                       VALUES ('ENTRADA', 'COMPRA', :usuario, :almox, :doc, :obs) RETURNING id";
            $stmtMov = $db->prepare($sqlMov);
            $stmtMov->execute([
                'usuario' => $usuarioId,
                'almox'   => $data['almoxarifado_id'],
                'doc'     => $data['documento'] ?? null,
                'obs'     => $data['observacao'] ?? null
            ]);
            $movimentacaoId = $stmtMov->fetchColumn();

            // 2. Insere os itens (a trigger do banco vai atualizar o saldo_estoque automaticamente)
            $sqlItem = "INSERT INTO itens_movimentacao (movimentacao_id, produto_id, quantidade, valor_unitario) 
                        VALUES (:mov_id, :produto, :qtd, :valor)";
            $stmtItem = $db->prepare($sqlItem);
            $stmtItem->execute([
                'mov_id'  => $movimentacaoId,
                'produto' => $data['produto_id'],
                'qtd'     => $data['quantidade'],
                'valor'   => !empty($data['valor_unitario']) ? $data['valor_unitario'] : null
            ]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e; // Repassa o erro para o Controller
        }
    }

    public function listarRecentes(): array
    {
        $sql = "SELECT m.id, m.data_movimentacao, m.tipo, m.subtipo, m.documento, 
                       u.nome AS usuario, a.nome AS almoxarifado
                  FROM movimentacoes m
                  JOIN usuarios u ON u.id = m.usuario_id
                  LEFT JOIN almoxarifados a ON a.id = m.almoxarifado_destino_id
                 ORDER BY m.id DESC LIMIT 50";
        return Database::getConnection()->query($sql)->fetchAll();
    }

        // Método auxiliar para consultar o saldo atual
    public function getSaldo(int $produtoId, int $almoxarifadoId): float
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT quantidade FROM saldo_estoque WHERE produto_id = :p AND almoxarifado_id = :a"
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
                'almox'   => $data['almoxarifado_id'],
                'doc'     => $data['documento'] ?? null,
                'obs'     => $data['observacao'] ?? null
            ]);
            $movimentacaoId = $stmtMov->fetchColumn();

            // 3. Insere os itens (A trigger do banco vai subtrair do saldo_estoque)
            $sqlItem = "INSERT INTO itens_movimentacao (movimentacao_id, produto_id, quantidade) 
                        VALUES (:mov_id, :produto, :qtd)";
            $stmtItem = $db->prepare($sqlItem);
            $stmtItem->execute([
                'mov_id'  => $movimentacaoId,
                'produto' => $data['produto_id'],
                'qtd'     => $data['quantidade']
            ]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}