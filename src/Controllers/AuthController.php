<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Usuario;

class AuthController
{
    public function loginAction(): void
    {
        // Se o usuário JÁ estiver logado (ex: tentou acessar a tela de login de novo)
        if (\App\Core\Auth::check()) {
            $usuarioLogado = \App\Core\Auth::user(); // Pega os dados de quem já está na sessão

            if ($usuarioLogado['perfil'] === 'OPERADOR') {
                header('Location: ?controller=requisicao&action=list');
            } else {
                header('Location: ?controller=dashboard&action=index');
            }
            exit;
        }

        // Se NÃO estiver logado, mostra a tela de login
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

            if ($usuario['deve_trocar_senha']) {
                $_SESSION['msg_alerta'] = 'Por segurança, você precisa cadastrar uma nova senha.';
                header('Location: ?controller=auth&action=trocarSenha');
                exit;
            }

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

    // Funções para recuperação de senha
    public function forgotAction(): void
    {
        require __DIR__ . '/../Views/auth/forgot.php';
    }

    public function sendForgotAction(): void
    {
        $email = $_POST['email'] ?? '';
        $token = (new \App\Models\Usuario())->gerarTokenRecuperacao($email);

        if ($token) {
            // Tenta enviar o e-mail
            \App\Core\Mailer::enviarRecuperacaoSenha($email, $token);
            $_SESSION['msg_recuperacao'] = 'Se o e-mail estiver cadastrado, você receberá um link em instantes.';
        } else {
            // Por segurança, não avisamos se o e-mail não existe (evita que hackers testem e-mails)
            $_SESSION['msg_recuperacao'] = 'Se o e-mail estiver cadastrado, você receberá um link em instantes.';
        }

        header('Location: ?controller=auth&action=login');
        exit;
    }

    public function resetAction(array $query): void
    {
        $token = $query['token'] ?? '';

        // Valida o token antes de mostrar a tela
        $usuario = (new \App\Models\Usuario())->validarToken($token);

        if (!$usuario) {
            $_SESSION['erro_login'] = 'Link inválido ou expirado. Solicite a recuperação novamente.';
            header('Location: ?controller=auth&action=login');
            exit;
        }

        require __DIR__ . '/../Views/auth/reset.php';
    }

    public function updatePasswordAction(): void
    {
        $token = $_POST['token'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $confirma = $_POST['confirma_senha'] ?? '';

        if (empty($senha) || $senha !== $confirma) {
            $_SESSION['erro_reset'] = 'As senhas não coincidem ou estão em branco.';
            header('Location: ?controller=auth&action=reset&token=' . $token);
            exit;
        }

        $model = new \App\Models\Usuario();
        $usuario = $model->validarToken($token);

        if ($usuario) {
            $model->atualizarSenhaComToken($usuario['id'], $senha);
            $_SESSION['msg_sucesso_login'] = 'Senha alterada com sucesso! Faça login com sua nova senha.';
            header('Location: ?controller=auth&action=login');
            exit;
        }

        $_SESSION['erro_login'] = 'Erro ao redefinir a senha. Token inválido.';
        header('Location: ?controller=auth&action=login');
        exit;
    }

    public function trocarSenhaAction(): void
    {
        // Só acessa se estiver logado e realmente precisar trocar
        if (!\App\Core\Auth::check() || !\App\Core\Auth::user()['deve_trocar_senha']) {
            header('Location: ?controller=dashboard&action=index');
            exit;
        }
        require __DIR__ . '/../Views/auth/trocar_senha.php';
    }

    public function salvarTrocaSenhaAction(): void
    {
        $senha = $_POST['senha'] ?? '';
        $confirma = $_POST['confirma_senha'] ?? '';
        $user = \App\Core\Auth::user();

        if (empty($senha) || $senha !== $confirma) {
            $_SESSION['msg_erro'] = 'As senhas não coincidem ou estão em branco.';
            header('Location: ?controller=auth&action=trocarSenha');
            exit;
        }

        (new \App\Models\Usuario())->trocarPropriaSenha($user['id'], $senha);

        // Atualiza a sessão para remover a obrigatoriedade
        $_SESSION['usuario']['deve_trocar_senha'] = false;
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Senha alterada com sucesso! Bem-vindo(a).'];

        // Manda para a tela inicial correta
        if ($user['perfil'] === 'OPERADOR') {
            header('Location: ?controller=requisicao&action=list');
        } else {
            header('Location: ?controller=dashboard&action=index');
        }
        exit;
    }
}