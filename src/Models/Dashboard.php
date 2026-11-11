<?php
namespace App\Models;

use App\Core\Database;

class Dashboard
{
    public function getTotalProdutos(): int
    {
        return (int) Database::getConnection()->query("SELECT COUNT(*) FROM produtos WHERE ativo = TRUE")->fetchColumn();
    }

    public function getRequisicoesPendentes(): int
    {
        return (int) Database::getConnection()->query("SELECT COUNT(*) FROM requisicoes WHERE status = 'AGUARDANDO_APROVACAO'")->fetchColumn();
    }

    public function getRequisicoesAprovadas(): int
    {
        return (int) Database::getConnection()->query("SELECT COUNT(*) FROM requisicoes WHERE status = 'APROVADA'")->fetchColumn();
    }

    public function getProdutosEstoqueBaixo(): array
    {
        $sql = "SELECT p.codigo, p.descricao, 
                       COALESCE(SUM(s.quantidade), 0) AS quantidade_atual, 
                       p.estoque_minimo
                  FROM produtos p
                  LEFT JOIN saldo_estoque s ON s.produto_id = p.id
                 WHERE p.ativo = TRUE
                 GROUP BY p.id, p.codigo, p.descricao, p.estoque_minimo
                HAVING COALESCE(SUM(s.quantidade), 0) <= p.estoque_minimo
                 ORDER BY quantidade_atual ASC";
                 
        return Database::getConnection()->query($sql)->fetchAll();
    }
}