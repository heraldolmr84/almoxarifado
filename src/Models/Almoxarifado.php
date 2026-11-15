<?php
namespace App\Models;
use App\Core\Database;

class Almoxarifado
{
    public function all(): array {
        return Database::getConnection()->query("SELECT * FROM almoxarifados WHERE ativo = TRUE ORDER BY nome")->fetchAll();
    }
    public function find(int $id): ?array {
        $stmt = Database::getConnection()->prepare("SELECT * FROM almoxarifados WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }
    public function create(array $data): bool {
        $stmt = Database::getConnection()->prepare("INSERT INTO almoxarifados (nome, localizacao) VALUES (:nome, :loc)");
        return $stmt->execute(['nome' => $data['nome'], 'loc' => $data['localizacao']]);
    }
    public function update(int $id, array $data): bool {
        $stmt = Database::getConnection()->prepare("UPDATE almoxarifados SET nome = :nome, localizacao = :loc WHERE id = :id");
        return $stmt->execute(['nome' => $data['nome'], 'loc' => $data['localizacao'], 'id' => $id]);
    }
    public function delete(int $id): bool {
        $stmt = Database::getConnection()->prepare("UPDATE almoxarifados SET ativo = FALSE WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}