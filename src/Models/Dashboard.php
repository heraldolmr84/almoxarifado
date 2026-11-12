<?php
namespace App\Models;

use App\Core\Database;

class Dashboard
{
    public function getTotalProdutos(): int
    {
        return (int) Database::getConnection()->query("SELECT COUNT(*) FROM produtos WHERE ativo = TRUE")->fetchColumn();
    }

    public function getRequisicoesPendentes(array $almoxPermitidos = []): int
    {
        $sql = "SELECT COUNT(*) FROM requisicoes WHERE status = 'AGUARDANDO_APROVACAO'";
        if (!empty($almoxPermitidos)) {
            $inQuery = implode(',', array_map('intval', $almoxPermitidos));
            $sql .= " AND almoxarifado_id IN ($inQuery)";
        }
        return (int) Database::getConnection()->query($sql)->fetchColumn();
    }

    public function getRequisicoesAprovadas(array $almoxPermitidos = []): int
    {
        $sql = "SELECT COUNT(*) FROM requisicoes WHERE status = 'APROVADA'";
        if (!empty($almoxPermitidos)) {
            $inQuery = implode(',', array_map('intval', $almoxPermitidos));
            $sql .= " AND almoxarifado_id IN ($inQuery)";
        }
        return (int) Database::getConnection()->query($sql)->fetchColumn();
    }

    public function getProdutosEstoqueBaixo(array $almoxPermitidos = []): array
    {
        $sql = "SELECT p.codigo, p.descricao, 
                       COALESCE(SUM(s.quantidade), 0) AS quantidade_atual, 
                       p.estoque_minimo
                  FROM produtos p
                  LEFT JOIN saldo_estoque s ON s.produto_id = p.id";

        if (!empty($almoxPermitidos)) {
            $inQuery = implode(',', array_map('intval', $almoxPermitidos));
            $sql .= " AND s.almoxarifado_id IN ($inQuery)";
        }

        $sql .= " WHERE p.ativo = TRUE
                  GROUP BY p.id, p.codigo, p.descricao, p.estoque_minimo
                 HAVING COALESCE(SUM(s.quantidade), 0) <= p.estoque_minimo
                  ORDER BY quantidade_atual ASC";

        return Database::getConnection()->query($sql)->fetchAll();
    }

    public function getTotalBackorders(array $almoxPermitidos = []): int
    {
        $sql = "SELECT COUNT(*) FROM requisicoes_itens ri
                  JOIN requisicoes r ON r.id = ri.requisicao_id
                 WHERE r.status IN ('ATENDIDA_PARCIAL', 'AGUARDANDO_ESTOQUE')
                   AND ri.quantidade_atendida < ri.quantidade_aprovada";
        if (!empty($almoxPermitidos)) {
            $inQuery = implode(',', array_map('intval', $almoxPermitidos));
            $sql .= " AND r.almoxarifado_id IN ($inQuery)";
        }
        return (int) Database::getConnection()->query($sql)->fetchColumn();
    }

    // Função que retorna loter a vencer
    public function getLotesAVencer(): array
    {
        $sql = "SELECT p.descricao, l.numero_lote, l.validade, s.quantidade, a.nome AS almoxarifado
                  FROM lotes l
                  JOIN produtos p ON p.id = l.produto_id
                  JOIN saldo_estoque s ON s.lote_id = l.id
                  JOIN almoxarifados a ON a.id = s.almoxarifado_id
                 WHERE s.quantidade > 0 
                   AND l.validade IS NOT NULL
                   AND l.validade <= CURRENT_DATE + INTERVAL '30 days'
                 ORDER BY l.validade ASC";
        return Database::getConnection()->query($sql)->fetchAll();
    }
}