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

    public static function getAlmoxarifadosPermitidos(): array
    {
        $user = self::user();

        // Se for ADMIN, retorna um array vazio (que nas queries significa "todos")
        if ($user['perfil'] === 'ADMIN') {
            return [];
        }

        // Se não for admin e não tiver departamento, não vê nada
        if (empty($user['departamento_id'])) {
            return [-1];
        }

        // Busca os IDs dos almoxarifados vinculados ao departamento do usuário
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT almoxarifado_id FROM departamentos_almoxarifados WHERE departamento_id = :dep_id");
        $stmt->execute(['dep_id' => $user['departamento_id']]);

        $ids = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        // Se não tiver nenhum vinculado, retorna [-1] para não quebrar o SQL
        return empty($ids) ? [-1] : $ids;
    }
}