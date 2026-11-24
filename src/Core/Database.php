<?php
namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $config = require __DIR__ . '/../../config/database.php';
            $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
            self::$connection = new PDO($dsn, $config['user'], $config['password'], $config['options']);

            // ====================================================
            // AUDITORIA:
            // Avisa o banco de dados qual é o ID do usuário logado
            // ====================================================
            if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['usuario']['id'])) {
                $userId = (int) $_SESSION['usuario']['id'];
                self::$connection->exec("SET app.current_user_id = {$userId}");
            }
        }
        return self::$connection;
    }
}