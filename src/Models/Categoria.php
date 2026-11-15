<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Categoria
{
    public function all(): array
    {
        $stmt = Database::getConnection()->query("
            SELECT c.*, p.nome AS pai_nome, c.prefixo_sku AS prefixo
              FROM categorias c
              LEFT JOIN categorias p ON p.id = c.categoria_pai_id
             WHERE c.ativo = TRUE
             ORDER BY c.nome
        ");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare("SELECT * FROM categorias WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $cat = $stmt->fetch();
        return $cat ?: null;
    }

    // Busca todas as categorias exceto a atual (para não ser pai de si mesma na edição)
    public function getPossiveisPais(int $ignorarId = 0): array
    {
        $sql = "SELECT id, nome FROM categorias WHERE ativo = TRUE";
        if ($ignorarId > 0) {
            $sql .= " AND id != :id";
        }
        $sql .= " ORDER BY nome";
        
        $stmt = Database::getConnection()->prepare($sql);
        if ($ignorarId > 0) {
            $stmt->bindValue(':id', $ignorarId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $data): bool
    {
        $stmt = Database::getConnection()->prepare("INSERT INTO categorias (nome, categoria_pai_id, prefixo_sku) VALUES (:nome, :pai, :sku)");
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':pai', !empty($data['categoria_pai_id']) ? $data['categoria_pai_id'] : null);
        $stmt->bindValue(':sku', $data['sku']);
        return $stmt->execute();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = Database::getConnection()->prepare("UPDATE categorias SET nome = :nome, categoria_pai_id = :pai, prefixo_sku = :sku WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':pai', !empty($data['categoria_pai_id']) ? $data['categoria_pai_id'] : null);
        $stmt->bindValue(':sku', $data['sku']);
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = Database::getConnection()->prepare("UPDATE categorias SET ativo = FALSE WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}