<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Estoque
{
    public function getEstoqueAtual(array $filtros = [], array $almoxPermitidos = [], int $limit = 8, int $offset = 0): array
    {
        $sql = "SELECT s.id, s.quantidade, p.codigo, p.descricao, p.estoque_minimo, 
                       a.nome AS almoxarifado, u.sigla AS unidade,
                       l.numero_lote, l.validade
                  FROM saldo_estoque s
                  JOIN produtos p ON p.id = s.produto_id
                  JOIN almoxarifados a ON a.id = s.almoxarifado_id
                  JOIN unidades_medida u ON u.id = p.unidade_medida_id
                  LEFT JOIN lotes l ON l.id = s.lote_id
                 WHERE s.quantidade > 0"; // Só mostra o que tem em estoque
                 
        $params = [];

        // Isolamento de segurança: Almoxarife só vê seus almoxarifados
        if (!empty($almoxPermitidos)) {
            $inQuery = implode(',', array_map('intval', $almoxPermitidos));
            $sql .= " AND s.almoxarifado_id IN ($inQuery)";
        }

        // Filtros
        if (!empty($filtros['codigo'])) {
            $sql .= " AND p.codigo ILIKE :codigo";
            $params['codigo'] = '%' . $filtros['codigo'] . '%';
        }
        if (!empty($filtros['descricao'])) {
            $sql .= " AND p.descricao ILIKE :descricao";
            $params['descricao'] = '%' . $filtros['descricao'] . '%';
        }
        if (!empty($filtros['almoxarifado_id'])) {
            $sql .= " AND s.almoxarifado_id = :almox";
            $params['almox'] = $filtros['almoxarifado_id'];
        }

        $sql .= " ORDER BY p.descricao, a.nome LIMIT :limit OFFSET :offset";
        
        $stmt = Database::getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function countEstoque(array $filtros = [], array $almoxPermitidos = []): int
    {
        $sql = "SELECT COUNT(*) FROM saldo_estoque s 
                JOIN produtos p ON p.id = s.produto_id 
                WHERE s.quantidade > 0";
        $params = [];

        if (!empty($almoxPermitidos)) {
            $inQuery = implode(',', array_map('intval', $almoxPermitidos));
            $sql .= " AND s.almoxarifado_id IN ($inQuery)";
        }

        if (!empty($filtros['codigo'])) {
            $sql .= " AND p.codigo ILIKE :codigo";
            $params['codigo'] = '%' . $filtros['codigo'] . '%';
        }
        if (!empty($filtros['descricao'])) {
            $sql .= " AND p.descricao ILIKE :descricao";
            $params['descricao'] = '%' . $filtros['descricao'] . '%';
        }
        if (!empty($filtros['almoxarifado_id'])) {
            $sql .= " AND s.almoxarifado_id = :almox";
            $params['almox'] = $filtros['almoxarifado_id'];
        }

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getAlmoxarifadosPermitidos(): array
    {
        $permissoes = \App\Core\Auth::getAlmoxarifadosPermitidos();
        if (empty($permissoes)) {
            return Database::getConnection()->query("SELECT id, nome FROM almoxarifados WHERE ativo = TRUE ORDER BY nome")->fetchAll();
        }
        
        $inQuery = implode(',', array_map('intval', $permissoes));
        return Database::getConnection()->query("SELECT id, nome FROM almoxarifados WHERE id IN ($inQuery) AND ativo = TRUE ORDER BY nome")->fetchAll();
    }
}