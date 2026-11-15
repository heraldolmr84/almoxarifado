<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class UnidadeMedida
{
    public function all(): array
    {
        return Database::getConnection()->query("SELECT * FROM unidades_medida ORDER BY sigla")->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare("SELECT * FROM unidades_medida WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = Database::getConnection()->prepare("INSERT INTO unidades_medida (sigla, descricao) VALUES (:sigla, :descricao)");
        return $stmt->execute(['sigla' => strtoupper($data['sigla']), 'descricao' => $data['descricao']]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = Database::getConnection()->prepare("UPDATE unidades_medida SET sigla = :sigla, descricao = :descricao WHERE id = :id");
        return $stmt->execute(['id' => $id, 'sigla' => strtoupper($data['sigla']), 'descricao' => $data['descricao']]);
    }

    public function delete(int $id): bool
    {
        $stmt = Database::getConnection()->prepare("DELETE FROM unidades_medida WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}