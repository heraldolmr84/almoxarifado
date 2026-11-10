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
}