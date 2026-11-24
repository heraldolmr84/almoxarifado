<?php
namespace App\Core;

class Csrf
{
    public static function gerarToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function input(): string
    {
        $token = self::gerarToken();
        return "<input type='hidden' name='_csrf_token' value='{$token}'>";
    }

    public static function validar(string $token): bool
    {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}