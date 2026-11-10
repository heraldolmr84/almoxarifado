<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Usuario;

class AuthController
{
    public function loginAction(): void
    {
        // Se já estiver logado, manda para a home
        if (Auth::check()) {
            header('Location: ?controller=produto&action=list');
            exit;
        }
        
        // Não usamos o layout padrão (header/footer) aqui, pois a tela de login é diferente
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function authenticateAction(): void
    {
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            $_SESSION['erro_login'] = 'Preencha todos os campos.';
            header('Location: ?controller=auth&action=login');
            exit;
        }

        $usuario = (new Usuario())->findByEmail($email);

        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            Auth::login($usuario);
            header('Location: ?controller=produto&action=list');
            exit;
        }

        $_SESSION['erro_login'] = 'E-mail ou senha incorretos.';
        header('Location: ?controller=auth&action=login');
        exit;
    }

    public function logoutAction(): void
    {
        Auth::logout();
        header('Location: ?controller=auth&action=login');
        exit;
    }
}