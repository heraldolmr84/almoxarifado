<?php
namespace App\Controllers;

use App\Models\Produto;

class ProdutoController
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
        $model = new Produto();
        
        $filtros = [
            'codigo' => $_GET['codigo'] ?? '',
            'descricao' => $_GET['descricao'] ?? '',
            'categoria_id' => $_GET['categoria_id'] ?? ''
        ];

        // --- INÍCIO DA LÓGICA DE PAGINAÇÃO ---
        $limit = 10; // Quantos itens por página
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        
        $offset = ($page - 1) * $limit;
        
        $produtos = $model->all($filtros, $limit, $offset);
        $totalRegistros = $model->countAll($filtros);
        $totalPaginas = ceil($totalRegistros / $limit);
        // --- FIM DA LÓGICA DE PAGINAÇÃO ---

        $mensagem = $_SESSION['mensagem'] ?? null;
        unset($_SESSION['mensagem']);
        
        $this->render('produtos/list', [
            'produtos' => $produtos,
            'categorias' => $model->getCategorias(),
            'mensagem' => $mensagem,
            'filtros' => $filtros,
            'page' => $page,
            'totalPaginas' => $totalPaginas
        ]);
    }

    public function editAction(array $query): void
    {
        $id = (int) ($query['id'] ?? 0);
        $model = new Produto();
        $produto = $model->find($id);

        if (!$produto) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Produto não encontrado.'];
            header('Location: ?controller=produto&action=list');
            exit;
        }

        $this->render('produtos/form', [
            'produto' => $produto,
            'categorias' => $model->getCategorias(),
            'unidades' => $model->getUnidades()
        ]);
    }

    public function updateAction(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        
        if (empty($_POST['codigo']) || empty($_POST['descricao']) || empty($_POST['unidade_medida_id'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Preencha os campos obrigatórios.'];
            header("Location: ?controller=produto&action=edit&id={$id}");
            exit;
        }

        try {
            (new Produto())->update($id, $_POST);
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Produto atualizado com sucesso!'];
        } catch (\PDOException $e) {
            $msg = $e->getCode() == 23505 ? 'Já existe um produto com este código.' : 'Erro ao atualizar: ' . $e->getMessage();
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => $msg];
        }
        
        header('Location: ?controller=produto&action=list');
        exit;
    }

    public function deleteAction(array $query): void
    {
        $id = (int) ($query['id'] ?? 0);
        
        try {
            (new Produto())->delete($id);
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Produto desativado com sucesso!'];
        } catch (\PDOException $e) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao desativar o produto.'];
        }
        
        header('Location: ?controller=produto&action=list');
        exit;
    }

    public function createAction(): void
    {
        $model = new Produto();
        $categorias = $model->getCategorias();
        $unidades = $model->getUnidades();
        
        $this->render('produtos/form', [
            'categorias' => $categorias,
            'unidades' => $unidades
        ]);
    }

    public function storeAction(): void
    {
        // Validação básica
        if (empty($_POST['codigo']) || empty($_POST['descricao']) || empty($_POST['unidade_medida_id'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Preencha os campos obrigatórios (Código, Descrição e Unidade).'];
            header('Location: ?controller=produto&action=create');
            exit;
        }

        try {
            (new Produto())->create($_POST);
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Produto cadastrado com sucesso!'];
            header('Location: ?controller=produto&action=list');
            exit;
        } catch (\PDOException $e) {

            // Vamos forçar a mostrar o erro real do Postgres na tela
            //die("ERRO NO BANCO DE DADOS: " . $e->getMessage());
            // Erro comum: código duplicado (SKU)
            $msg = $e->getCode() == 23505 ? 'Já existe um produto com este código.' : 'Erro ao cadastrar.';
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => $msg];
            header('Location: ?controller=produto&action=create');
            exit;
        }
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . "/../Views/{$view}.php";
        require __DIR__ . '/../Views/layout/footer.php';
    }
}