<?php
namespace App\Controllers;

use App\Models\Fornecedor;
use App\Core\Auth;

class FornecedorController
{
    public function __construct()
    {
        $user = \App\Core\Auth::user();
        if (!in_array($user['perfil'], ['ADMIN', 'ALMOXARIFE'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Acesso negado.'];
            header('Location: ?controller=pedidocompra&action=list');
            exit;
        }
    }

    public function listAction(): void
    {
        $fornecedores = (new Fornecedor())->all();
        $mensagem = $_SESSION['mensagem'] ?? null;
        unset($_SESSION['mensagem']);
        $this->render('fornecedores/list', ['fornecedores' => $fornecedores, 'mensagem' => $mensagem]);
    }

    public function createAction(): void
    {
        // Lê e apaga a mensagem da sessão
        $mensagem = $_SESSION['mensagem'] ?? null;
        unset($_SESSION['mensagem']);

        $this->render('fornecedores/form', ['mensagem' => $mensagem]);
    }

    public function storeAction(): void
    {
        // 1. VALIDAÇÕES (Antes de tentar salvar)
        if (!empty($_POST['email']) && !\App\Core\Validator::email($_POST['email'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'E-mail inválido.'];
            header('Location: ?controller=fornecedor&action=create');
            exit;
        }

        if (!empty($_POST['cnpj']) && !\App\Core\Validator::cnpjCpf($_POST['cnpj'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'CNPJ ou CPF inválido.'];
            header('Location: ?controller=fornecedor&action=create');
            exit;
        }

        // 2. LIMPEZA (Salvar apenas os números)
        $_POST['cnpj'] = !empty($_POST['cnpj']) ? preg_replace('/[^0-9]/', '', $_POST['cnpj']) : null;
        $_POST['telefone'] = !empty($_POST['telefone']) ? preg_replace('/[^0-9]/', '', $_POST['telefone']) : null;

        try {
            (new Fornecedor())->create($_POST);
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Fornecedor cadastrado!'];
            header('Location: ?controller=fornecedor&action=list');
        } catch (\PDOException $e) {
            $msg = $e->getCode() == 23505 ? 'CNPJ já cadastrado.' : 'Erro ao cadastrar.';
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => $msg];
            header('Location: ?controller=fornecedor&action=create');
        }
        exit;
    }

    public function editAction(array $query): void
    {
        $fornecedor = (new Fornecedor())->find((int) ($query['id'] ?? 0));
        if (!$fornecedor) {
            header('Location: ?controller=fornecedor&action=list');
            exit;
        }

        // Lê e apaga a mensagem da sessão
        $mensagem = $_SESSION['mensagem'] ?? null;
        unset($_SESSION['mensagem']);

        $this->render('fornecedores/form', [
            'fornecedor' => $fornecedor,
            'mensagem' => $mensagem
        ]);
    }

    public function updateAction(): void
    {
        // 1. VALIDAÇÕES (Antes de tentar atualizar)
        if (!empty($_POST['email']) && !\App\Core\Validator::email($_POST['email'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'E-mail inválido.'];
            header('Location: ?controller=fornecedor&action=edit&id=' . $_POST['id']);
            exit;
        }

        if (!empty($_POST['cnpj']) && !\App\Core\Validator::cnpjCpf($_POST['cnpj'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'CNPJ ou CPF inválido.'];
            header('Location: ?controller=fornecedor&action=edit&id=' . $_POST['id']);
            exit;
        }

        // 2. LIMPEZA
        $_POST['cnpj'] = !empty($_POST['cnpj']) ? preg_replace('/[^0-9]/', '', $_POST['cnpj']) : null;
        $_POST['telefone'] = !empty($_POST['telefone']) ? preg_replace('/[^0-9]/', '', $_POST['telefone']) : null;

        try {
            (new Fornecedor())->update((int) $_POST['id'], $_POST);
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Fornecedor atualizado!'];
        } catch (\PDOException $e) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao atualizar (CNPJ duplicado?).'];
        }
        header('Location: ?controller=fornecedor&action=list');
        exit;
    }

    public function deleteAction(array $query): void
    {
        (new Fornecedor())->delete((int) ($query['id'] ?? 0));
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Fornecedor desativado!'];
        header('Location: ?controller=fornecedor&action=list');
        exit;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . "/../Views/{$view}.php";
        require __DIR__ . '/../Views/layout/footer.php';
    }
}