<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Produto
{
    public function all(array $filtros = [], int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT p.*, c.nome AS categoria, u.sigla AS unidade
                  FROM produtos p
                  LEFT JOIN categorias c ON c.id = p.categoria_id
                  JOIN unidades_medida u ON u.id = p.unidade_medida_id
                 WHERE p.ativo = TRUE";

        $params = [];

        if (!empty($filtros['codigo'])) {
            $sql .= " AND p.codigo ILIKE :codigo";
            $params['codigo'] = '%' . $filtros['codigo'] . '%';
        }
        if (!empty($filtros['descricao'])) {
            $sql .= " AND p.descricao ILIKE :descricao";
            $params['descricao'] = '%' . $filtros['descricao'] . '%';
        }
        if (!empty($filtros['categoria_id'])) {
            $sql .= " AND p.categoria_id = :categoria_id";
            $params['categoria_id'] = $filtros['categoria_id'];
        }

        // Adicionando a paginação na query
        $sql .= " ORDER BY p.descricao LIMIT :limit OFFSET :offset";

        $stmt = Database::getConnection()->prepare($sql);

        // Bind dos filtros normais
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        // O PostgreSQL exige que LIMIT e OFFSET sejam tratados como INT
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(array $filtros = []): int
    {
        $sql = "SELECT COUNT(*) FROM produtos p WHERE p.ativo = TRUE";
        $params = [];

        if (!empty($filtros['codigo'])) {
            $sql .= " AND p.codigo ILIKE :codigo";
            $params['codigo'] = '%' . $filtros['codigo'] . '%';
        }
        if (!empty($filtros['descricao'])) {
            $sql .= " AND p.descricao ILIKE :descricao";
            $params['descricao'] = '%' . $filtros['descricao'] . '%';
        }
        if (!empty($filtros['categoria_id'])) {
            $sql .= " AND p.categoria_id = :categoria_id";
            $params['categoria_id'] = $filtros['categoria_id'];
        }

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getCategorias(): array
    {
        $stmt = Database::getConnection()->query("SELECT id, nome FROM categorias WHERE ativo = TRUE ORDER BY nome");
        return $stmt->fetchAll();
    }

    public function getUnidades(): array
    {
        $stmt = Database::getConnection()->query("SELECT id, sigla FROM unidades_medida ORDER BY sigla");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare("SELECT * FROM produtos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $produto = $stmt->fetch();
        return $produto ?: null;
    }

    public function create(array $data): bool
    {

        // 1. Resolve o fornecedor (busca ou cria)
        $fornecedorId = null;
        if (!empty($data['fornecedor_nome'])) {
            $fornecedorId = (new Fornecedor())->findOrCreate($data['fornecedor_nome']);
        }

        $sql = "INSERT INTO produtos (codigo, descricao, categoria_id, unidade_medida_id, estoque_minimo, controla_lote, fornecedor_id)
                VALUES (:codigo, :descricao, :categoria_id, :unidade_medida_id, :estoque_minimo, :controla_lote, :fornecedor_id)";

        $stmt = Database::getConnection()->prepare($sql);

        // Vinculando os parâmetros explicitamente
        $stmt->bindValue(':codigo', $data['codigo']);
        $stmt->bindValue(':descricao', $data['descricao']);

        if (!empty($data['categoria_id'])) {
            $stmt->bindValue(':categoria_id', $data['categoria_id'], PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':categoria_id', null, PDO::PARAM_NULL);
        }

        $stmt->bindValue(':unidade_medida_id', $data['unidade_medida_id'], PDO::PARAM_INT);
        $stmt->bindValue(':estoque_minimo', !empty($data['estoque_minimo']) ? $data['estoque_minimo'] : 0);

        // AQUI ESTÁ A CORREÇÃO: Forçando o envio como Booleano nativo para o Postgres
        $stmt->bindValue(':controla_lote', isset($data['controla_lote']), PDO::PARAM_BOOL);
        $stmt->bindValue(':fornecedor_id', $fornecedorId, $fornecedorId ? PDO::PARAM_INT : PDO::PARAM_NULL);

        return $stmt->execute();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE produtos 
                   SET codigo = :codigo, 
                       descricao = :descricao, 
                       categoria_id = :categoria_id, 
                       unidade_medida_id = :unidade_medida_id, 
                       estoque_minimo = :estoque_minimo, 
                       controla_lote = :controla_lote
                 WHERE id = :id";

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':codigo', $data['codigo']);
        $stmt->bindValue(':descricao', $data['descricao']);

        if (!empty($data['categoria_id'])) {
            $stmt->bindValue(':categoria_id', $data['categoria_id'], PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':categoria_id', null, PDO::PARAM_NULL);
        }

        $stmt->bindValue(':unidade_medida_id', $data['unidade_medida_id'], PDO::PARAM_INT);
        $stmt->bindValue(':estoque_minimo', !empty($data['estoque_minimo']) ? $data['estoque_minimo'] : 0);
        $stmt->bindValue(':controla_lote', isset($data['controla_lote']), PDO::PARAM_BOOL);

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = Database::getConnection()->prepare("UPDATE produtos SET ativo = FALSE WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}