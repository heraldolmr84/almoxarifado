<?php
namespace App\Controllers;

use App\Models\Usuario;
use App\Core\Auth;

class UsuarioController
{
    public function __construct()
    {
        // Trava de segurança: Apenas Admin
        if (Auth::user()['perfil'] !== 'ADMIN') {
            die("Acesso negado. Apenas administradores podem gerenciar usuários.");
        }
    }

    public function listAction(): void
    {
        $usuarios = (new Usuario())->all();
        $mensagem = $_SESSION['mensagem'] ?? null; unset($_SESSION['mensagem']);
        $this->render('usuarios/list', ['usuarios' => $usuarios, 'mensagem' => $mensagem]);
    }

    public function createAction(): void
    {
        // Pega os departamentos para preencher o select
        $departamentos = \App\Core\Database::getConnection()->query("SELECT id, nome FROM departamentos WHERE ativo = TRUE ORDER BY nome")->fetchAll();
        $this->render('usuarios/form', ['departamentos' => $departamentos]);
    }

    public function storeAction(): void
    {
        if (empty($_POST['nome']) || empty($_POST['email']) || empty($_POST['senha']) || empty($_POST['perfil'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Preencha todos os campos obrigatórios.'];
            header('Location: ?controller=usuario&action=create'); exit;
        }

        try {
            (new Usuario())->create($_POST);
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Usuário cadastrado!'];
            header('Location: ?controller=usuario&action=list');
        } catch (\PDOException $e) {
            $msg = $e->getCode() == 23505 ? 'Este e-mail já está cadastrado.' : 'Erro ao cadastrar.';
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => $msg];
            header('Location: ?controller=usuario&action=create');
        }
        exit;
    }

    public function editAction(array $query): void
    {
        $usuario = (new Usuario())->find((int) ($query['id'] ?? 0));
        $departamentos = \App\Core\Database::getConnection()->query("SELECT id, nome FROM departamentos WHERE ativo = TRUE ORDER BY nome")->fetchAll();
        
        if (!$usuario) { header('Location: ?controller=usuario&action=list'); exit; }
        $this->render('usuarios/form', ['usuario' => $usuario, 'departamentos' => $departamentos]);
    }

    public function updateAction(): void
    {
        try {
            (new Usuario())->update((int) $_POST['id'], $_POST);
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Usuário atualizado!'];
        } catch (\PDOException $e) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao atualizar (e-mail duplicado?).'];
        }
        header('Location: ?controller=usuario&action=list'); exit;
    }

    public function deleteAction(array $query): void
    {
        $id = (int) ($query['id'] ?? 0);
        if ($id === Auth::user()['id']) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Você não pode excluir o seu próprio usuário!'];
        } else {
            (new Usuario())->delete($id);
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Usuário desativado!'];
        }
        header('Location: ?controller=usuario&action=list'); exit;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . "/../Views/{$view}.php";
        require __DIR__ . '/../Views/layout/footer.php';
    }
}