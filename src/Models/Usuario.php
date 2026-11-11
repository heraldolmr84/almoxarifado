<?php
namespace App\Models;

use App\Core\Database;

class Usuario
{
    public function findByEmail(string $email): ?array
    {
        $stmt = Database::getConnection()->prepare("SELECT * FROM usuarios WHERE email = :email AND ativo = TRUE");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        
        return $user ?: null;
    }

    public function all(): array
    {
        return Database::getConnection()->query("SELECT id, nome, email, perfil, ativo FROM usuarios WHERE ativo = TRUE ORDER BY nome")->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo) VALUES (:nome, :email, :senha, :perfil, TRUE)";
        $stmt = Database::getConnection()->prepare($sql);
        return $stmt->execute([
            'nome'   => $data['nome'],
            'email'  => $data['email'],
            'senha'  => password_hash($data['senha'], PASSWORD_DEFAULT),
            'perfil' => $data['perfil']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        // Se o campo senha estiver vazio, atualiza apenas os outros dados
        if (empty($data['senha'])) {
            $sql = "UPDATE usuarios SET nome = :nome, email = :email, perfil = :perfil WHERE id = :id";
            $stmt = Database::getConnection()->prepare($sql);
            return $stmt->execute(['nome' => $data['nome'], 'email' => $data['email'], 'perfil' => $data['perfil'], 'id' => $id]);
        } else {
            // Se a senha foi preenchida, atualiza ela também
            $sql = "UPDATE usuarios SET nome = :nome, email = :email, perfil = :perfil, senha_hash = :senha WHERE id = :id";
            $stmt = Database::getConnection()->prepare($sql);
            return $stmt->execute([
                'nome' => $data['nome'], 'email' => $data['email'], 'perfil' => $data['perfil'],
                'senha' => password_hash($data['senha'], PASSWORD_DEFAULT), 'id' => $id
            ]);
        }
    }

    public function delete(int $id): bool
    {
        // Soft delete para manter o histórico de quem fez o quê
        $stmt = Database::getConnection()->prepare("UPDATE usuarios SET ativo = FALSE WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}