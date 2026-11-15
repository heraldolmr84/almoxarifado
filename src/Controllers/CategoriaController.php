<?php
namespace App\Controllers;

use App\Models\Categoria;

class CategoriaController
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
        $categorias = (new Categoria())->all();
        $mensagem = $_SESSION['mensagem'] ?? null;
        unset($_SESSION['mensagem']);
        
        $this->render('categorias/list', ['categorias' => $categorias, 'mensagem' => $mensagem]);
    }

    public function createAction(): void
    {
        $pais = (new Categoria())->getPossiveisPais();
        $this->render('categorias/form', ['pais' => $pais]);
    }

    public function storeAction(): void
    {
        if (empty($_POST['nome'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'O nome da categoria é obrigatório.'];
            header('Location: ?controller=categoria&action=create');
            exit;
        }

        try {
            (new Categoria())->create($_POST);
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Categoria cadastrada com sucesso!'];
            header('Location: ?controller=categoria&action=list');
        } catch (\PDOException $e) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao cadastrar: ' . $e->getMessage()];
            header('Location: ?controller=categoria&action=create');
        }
        exit;
    }

    public function editAction(array $query): void
    {
        $id = (int) ($query['id'] ?? 0);
        $model = new Categoria();
        $categoria = $model->find($id);

        if (!$categoria) {
            header('Location: ?controller=categoria&action=list');
            exit;
        }

        $this->render('categorias/form', [
            'categoria' => $categoria,
            'pais' => $model->getPossiveisPais($id)
        ]);
    }

    public function updateAction(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        
        try {
            (new Categoria())->update($id, $_POST);
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Categoria atualizada com sucesso!'];
        } catch (\PDOException $e) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao atualizar.'];
        }
        header('Location: ?controller=categoria&action=list');
        exit;
    }

    public function deleteAction(array $query): void
    {
        $id = (int) ($query['id'] ?? 0);
        (new Categoria())->delete($id);
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Categoria desativada com sucesso!'];
        header('Location: ?controller=categoria&action=list');
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