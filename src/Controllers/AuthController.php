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

            // Redirecionamento baseado no perfil
            if ($usuario['perfil'] === 'OPERADOR') {
                header('Location: ?controller=requisicao&action=list');
            } else {
                header('Location: ?controller=dashboard&action=index');
            }
            // Substitua as verificações de perfil por esta linha única:
            //header('Location: ?controller=dashboard&action=index');
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
            // Redirecionamento baseado no perfil
            if ($usuario['perfil'] === 'OPERADOR') {
                header('Location: ?controller=requisicao&action=list');
            } else {
                header('Location: ?controller=dashboard&action=index');
            }
            // Substitua as verificações de perfil por esta linha única:
            //header('Location: ?controller=dashboard&action=index');
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