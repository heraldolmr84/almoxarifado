<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Auth;

class AuditoriaController
{
    public function __construct()
    {
        // Trava de segurança: Apenas Admin
        if (Auth::user()['perfil'] !== 'ADMIN') {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Acesso negado.'];
            header('Location: ?controller=dashboard&action=index');
            exit;
        }
    }

    public function listAction(): void
    {
        $db = Database::getConnection();
        
        // Busca os logs e faz um JOIN com a tabela de usuários para pegar o nome
        $sql = "SELECT a.*, u.nome AS usuario_nome 
                  FROM auditoria a
                  LEFT JOIN usuarios u ON u.id = a.usuario_id
                 ORDER BY a.id DESC LIMIT 100";
                 
        $dados = $db->query($sql)->fetchAll();
        
        $this->render('auditoria/list', ['dados' => $dados]);
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . "/../Views/{$view}.php";
        require __DIR__ . '/../Views/layout/footer.php';
    }
}