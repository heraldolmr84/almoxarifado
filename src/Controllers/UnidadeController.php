<?php
namespace App\Controllers;

use App\Models\UnidadeMedida;

class UnidadeController
{

    public function __construct()
    {
        $user = \App\Core\Auth::user();
        if ($user['perfil'] === 'OPERADOR') {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Acesso negado. Você não tem permissão para gerenciar o catálogo.'];
            header('Location: ?controller=requisicao&action=list');
            exit;
        }
    }

    public function listAction(): void
    {
        $unidades = (new UnidadeMedida())->all();
        $mensagem = $_SESSION['mensagem'] ?? null;
        unset($_SESSION['mensagem']);
        $this->render('unidades/list', ['unidades' => $unidades, 'mensagem' => $mensagem]);
    }

    public function createAction(): void
    {
        $this->render('unidades/form');
    }

    public function storeAction(): void
    {
        if (empty($_POST['sigla']) || empty($_POST['descricao'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Preencha todos os campos.'];
            header('Location: ?controller=unidade&action=create'); exit;
        }
        try {
            (new UnidadeMedida())->create($_POST);
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Unidade cadastrada!'];
            header('Location: ?controller=unidade&action=list');
        } catch (\PDOException $e) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro: Sigla já existe.'];
            header('Location: ?controller=unidade&action=create');
        }
        exit;
    }

    public function editAction(array $query): void
    {
        $unidade = (new UnidadeMedida())->find((int) ($query['id'] ?? 0));
        if (!$unidade) { header('Location: ?controller=unidade&action=list'); exit; }
        $this->render('unidades/form', ['unidade' => $unidade]);
    }

    public function updateAction(): void
    {
        try {
            (new UnidadeMedida())->update((int) $_POST['id'], $_POST);
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Unidade atualizada!'];
        } catch (\PDOException $e) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao atualizar: Sigla duplicada.'];
        }
        header('Location: ?controller=unidade&action=list'); exit;
    }

    public function deleteAction(array $query): void
    {
        try {
            (new UnidadeMedida())->delete((int) ($query['id'] ?? 0));
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Unidade excluída!'];
        } catch (\PDOException $e) {
            // Erro 23503 é violação de Foreign Key (tem produto usando essa unidade)
            $msg = $e->getCode() == 23503 ? 'Não é possível excluir: há produtos usando esta unidade.' : 'Erro ao excluir.';
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => $msg];
        }
        header('Location: ?controller=unidade&action=list'); exit;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . "/../Views/{$view}.php";
        require __DIR__ . '/../Views/layout/footer.php';
    }
}