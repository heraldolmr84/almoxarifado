<?php
namespace App\Core;

class Auth
{
    public static function login(array $usuario): void
    {
        // Previne fixação de sessão (Session Fixation)
        session_regenerate_id(true);
        
        $_SESSION['usuario'] = [
            'id' => $usuario['id'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'perfil' => $usuario['perfil'],
            'departamento_id' => $usuario['departamento_id'] 
        ];
    }

    public static function logout(): void
    {
        unset($_SESSION['usuario']);
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['usuario']);
    }

    public static function user(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }
}