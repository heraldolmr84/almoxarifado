<?php
namespace App\Controllers;

use App\Models\Departamento;
use App\Core\Auth;

class DepartamentoController
{
    public function __construct()
    {
        // Trava de segurança: Apenas Admin
        if (Auth::user()['perfil'] !== 'ADMIN') {
            die("Acesso negado. Apenas administradores podem gerenciar departamentos.");
        }
    }

    public function listAction(): void
    {
        $departamentos = (new Departamento())->all();
        $mensagem = $_SESSION['mensagem'] ?? null; unset($_SESSION['mensagem']);
        $this->render('departamentos/list', ['departamentos' => $departamentos, 'mensagem' => $mensagem]);
    }

    public function createAction(): void
    {
        $almoxarifados = (new Departamento())->getAlmoxarifados();
        $this->render('departamentos/form', ['almoxarifados' => $almoxarifados]);
    }

    public function storeAction(): void
    {
        if (empty($_POST['nome'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'O nome do departamento é obrigatório.'];
            header('Location: ?controller=departamento&action=create'); exit;
        }

        try {
            (new Departamento())->create($_POST);
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Departamento cadastrado!'];
            header('Location: ?controller=departamento&action=list');
        } catch (\PDOException $e) {
            $msg = $e->getCode() == 23505 ? 'Já existe um departamento com este nome.' : 'Erro ao cadastrar.';
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => $msg];
            header('Location: ?controller=departamento&action=create');
        }
        exit;
    }

    public function editAction(array $query): void
    {
        $model = new Departamento();
        $departamento = $model->find((int) ($query['id'] ?? 0));
        
        if (!$departamento) { header('Location: ?controller=departamento&action=list'); exit; }
        
        $this->render('departamentos/form', [
            'departamento' => $departamento,
            'almoxarifados' => $model->getAlmoxarifados()
        ]);
    }

    public function updateAction(): void
    {
        $id = (int) $_POST['id'];
        try {
            (new Departamento())->update($id, $_POST);
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Departamento atualizado!'];
        } catch (\PDOException $e) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao atualizar.'];
        }
        header('Location: ?controller=departamento&action=list'); exit;
    }

    public function deleteAction(array $query): void
    {
        (new Departamento())->delete((int) ($query['id'] ?? 0));
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Departamento desativado!'];
        header('Location: ?controller=departamento&action=list'); exit;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . "/../Views/{$view}.php";
        require __DIR__ . '/../Views/layout/footer.php';
    }
}