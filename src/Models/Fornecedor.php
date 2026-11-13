<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Fornecedor
{
    public function all(): array
    {
        return Database::getConnection()->query("SELECT * FROM fornecedores WHERE ativo = TRUE ORDER BY razao_social")->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare("SELECT * FROM fornecedores WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findOrCreate(string $nome): ?int
    {
        $nome = trim($nome);
        if (empty($nome)) return null;

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM fornecedores WHERE razao_social ILIKE :nome OR nome_fantasia ILIKE :nome LIMIT 1");
        $stmt->execute(['nome' => $nome]);
        $id = $stmt->fetchColumn();

        if ($id) return (int) $id;

        $stmt = $db->prepare("INSERT INTO fornecedores (razao_social) VALUES (:nome) RETURNING id");
        $stmt->execute(['nome' => $nome]);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO fornecedores (razao_social, nome_fantasia, cnpj, email, telefone) 
                VALUES (:razao, :fantasia, :cnpj, :email, :telefone)";
        $stmt = Database::getConnection()->prepare($sql);
        return $stmt->execute([
            'razao' => $data['razao_social'],
            'fantasia' => $data['nome_fantasia'] ?: null,
            'cnpj' => $data['cnpj'] ?: null,
            'email' => $data['email'] ?: null,
            'telefone' => $data['telefone'] ?: null
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE fornecedores 
                   SET razao_social = :razao, nome_fantasia = :fantasia, cnpj = :cnpj, email = :email, telefone = :telefone 
                 WHERE id = :id";
        $stmt = Database::getConnection()->prepare($sql);
        return $stmt->execute([
            'razao' => $data['razao_social'],
            'fantasia' => $data['nome_fantasia'] ?: null,
            'cnpj' => $data['cnpj'] ?: null,
            'email' => $data['email'] ?: null,
            'telefone' => $data['telefone'] ?: null,
            'id' => $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = Database::getConnection()->prepare("UPDATE fornecedores SET ativo = FALSE WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}