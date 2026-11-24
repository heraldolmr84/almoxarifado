<?php
namespace App\Models;

use App\Core\Database;
use PDO;

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

    public function getProdutosEstoqueBaixo(array $almoxPermitidos = [], int $limit = 5, int $offset = 0): array
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
                  ORDER BY quantidade_atual ASC LIMIT :limit OFFSET :offset";

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
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
    public function getLotesAVencer(int $limit = 5, int $offset = 0): array
    {
        $sql = "SELECT p.descricao, l.numero_lote, l.validade, s.quantidade, a.nome AS almoxarifado
                  FROM lotes l
                  JOIN produtos p ON p.id = l.produto_id
                  JOIN saldo_estoque s ON s.lote_id = l.id
                  JOIN almoxarifados a ON a.id = s.almoxarifado_id
                 WHERE s.quantidade > 0 
                   AND l.validade IS NOT NULL
                   AND l.validade <= CURRENT_DATE + INTERVAL '30 days'
                 ORDER BY l.validade ASC LIMIT :limit OFFSET :offset";

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countProdutosEstoqueBaixo(array $almoxPermitidos = []): int
    {
        // Para contar, precisamos envolver a query com HAVING em uma subquery
        $sql = "SELECT COUNT(*) FROM (
                    SELECT p.id FROM produtos p
                    LEFT JOIN saldo_estoque s ON s.produto_id = p.id";

        if (!empty($almoxPermitidos)) {
            $inQuery = implode(',', array_map('intval', $almoxPermitidos));
            $sql .= " AND s.almoxarifado_id IN ($inQuery)";
        }

        $sql .= " WHERE p.ativo = TRUE
                    GROUP BY p.id 
                   HAVING COALESCE(SUM(s.quantidade), 0) <= p.estoque_minimo
                ) AS sub";

        return (int) Database::getConnection()->query($sql)->fetchColumn();
    }

    public function countLotesAVencer(): int
    {
        $sql = "SELECT COUNT(*) FROM lotes l
                  JOIN saldo_estoque s ON s.lote_id = l.id
                 WHERE s.quantidade > 0 
                   AND l.validade IS NOT NULL
                   AND l.validade <= CURRENT_DATE + INTERVAL '30 days'";
        return (int) Database::getConnection()->query($sql)->fetchColumn();
    }

    /** Funções para Dashboard Gerencial */
    // 1. Total Gasto no Período
    public function getTotalGastoPeriodo(string $inicio, string $fim): float
    {
        $sql = "SELECT COALESCE(SUM(i.quantidade * i.valor_unitario), 0) 
                  FROM itens_movimentacao i
                  JOIN movimentacoes m ON m.id = i.movimentacao_id
                 WHERE m.tipo = 'ENTRADA' AND m.status = 'CONFIRMADA'
                   AND m.data_movimentacao >= :inicio AND m.data_movimentacao <= :fim";
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute(['inicio' => $inicio . ' 00:00:00', 'fim' => $fim . ' 23:59:59']);
        return (float) $stmt->fetchColumn();
    }

    // 2. Compras Agrupadas por Mês (Dentro do período)
    public function getComprasPorMes(string $inicio, string $fim): array
    {
        $sql = "SELECT TO_CHAR(date_trunc('month', m.data_movimentacao), 'MM/YYYY') AS mes,
                       SUM(i.quantidade * i.valor_unitario) AS total
                  FROM itens_movimentacao i
                  JOIN movimentacoes m ON m.id = i.movimentacao_id
                 WHERE m.tipo = 'ENTRADA' AND m.status = 'CONFIRMADA' AND i.valor_unitario > 0
                   AND m.data_movimentacao >= :inicio AND m.data_movimentacao <= :fim
                 GROUP BY date_trunc('month', m.data_movimentacao)
                 ORDER BY date_trunc('month', m.data_movimentacao) ASC";
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute(['inicio' => $inicio . ' 00:00:00', 'fim' => $fim . ' 23:59:59']);
        return $stmt->fetchAll();
    }

    // 3. Consumo por Departamento (Dentro do período)
    public function getConsumoPorDepartamento(string $inicio, string $fim): array
    {
        $sql = "SELECT d.nome AS departamento, SUM(ri.quantidade_atendida) AS total
                  FROM requisicoes_itens ri
                  JOIN requisicoes r ON r.id = ri.requisicao_id
                  JOIN departamentos d ON d.id = r.departamento_id
                 WHERE r.status IN ('ATENDIDA', 'ATENDIDA_PARCIAL')
                   AND r.criado_em >= :inicio AND r.criado_em <= :fim
                 GROUP BY d.nome
                 ORDER BY total DESC
                 LIMIT 5";
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute(['inicio' => $inicio . ' 00:00:00', 'fim' => $fim . ' 23:59:59']);
        return $stmt->fetchAll();
    }

    // 4. Movimentações Mensais (Entradas x Saídas no período)
    public function getMovimentacoesMensais(string $inicio, string $fim): array
    {
        $sql = "SELECT TO_CHAR(date_trunc('month', m.data_movimentacao), 'MM/YYYY') AS mes,
                       m.tipo,
                       SUM(i.quantidade) AS total
                  FROM movimentacoes m
                  JOIN itens_movimentacao i ON i.movimentacao_id = m.id
                 WHERE m.status = 'CONFIRMADA'
                   AND m.data_movimentacao >= :inicio AND m.data_movimentacao <= :fim
                 GROUP BY date_trunc('month', m.data_movimentacao), m.tipo
                 ORDER BY date_trunc('month', m.data_movimentacao) ASC";

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute(['inicio' => $inicio . ' 00:00:00', 'fim' => $fim . ' 23:59:59']);
        $dados = $stmt->fetchAll();

        $meses = [];
        $entradas = [];
        $saidas = [];
        $agrupado = [];
        foreach ($dados as $d) {
            $agrupado[$d['mes']][$d['tipo']] = (float) $d['total'];
        }

        foreach ($agrupado as $mes => $tipos) {
            $meses[] = $mes;
            $entradas[] = $tipos['ENTRADA'] ?? 0;
            $saidas[] = $tipos['SAIDA'] ?? 0;
        }

        return ['meses' => $meses, 'entradas' => $entradas, 'saidas' => $saidas];
    }
}