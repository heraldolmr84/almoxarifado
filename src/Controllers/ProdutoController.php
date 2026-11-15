<?php

namespace App\Controllers;

use App\Models\Produto;

class ProdutoController
{

    public function __construct()
    {
        $user = \App\Core\Auth::user();

        // Se não estiver logado, redireciona para o login
        if (!$user) {
            header('Location: ?controller=auth&action=login');
            exit;
        }

        // Se for Operador, bloqueia o acesso
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
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($page < 1)
            $page = 1;

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
            'unidades' => $model->getUnidades(),
            'fornecedores' => (new \App\Models\Fornecedor())->all()
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
            'unidades' => $unidades,
            'fornecedores' => (new \App\Models\Fornecedor())->all()
        ]);
    }

    public function storeAction(): void
    {
        // 1. REDE DE SEGURANÇA: Força a geração do SKU no PHP
        // Isso garante que, mesmo que o JavaScript falhe, o código será gerado.
        if (!empty($_POST['categoria_id'])) {
            $_POST['codigo'] = (new Produto())->gerarProximoSKU((int)$_POST['categoria_id']);
        }

        // 2. Validação básica
        if (empty($_POST['codigo']) || empty($_POST['descricao']) || empty($_POST['unidade_medida_id'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Preencha os campos obrigatórios (Código, Descrição e Unidade).'];
            header('Location: ?controller=produto&action=create');
            exit;
        }

        try {
            // REMOVIDO: A linha que gerava o SKU novamente aqui dentro foi apagada
            (new Produto())->create($_POST);

            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Produto cadastrado com sucesso!'];
            header('Location: ?controller=produto&action=list');
            exit;
        } catch (\PDOException $e) {
            // Se der erro de duplicidade (código 23505)
            $msg = $e->getCode() == 23505 ? 'Já existe um produto com este código.' : 'Erro ao cadastrar: ' . $e->getMessage();
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => $msg];
            header('Location: ?controller=produto&action=create');
            exit;
        }
    }

    public function apiProximoSkuAction(): void
    {
        // Limpa qualquer buffer de saída para não sujar o JSON/Texto
        ob_clean();

        $categoriaId = isset($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : 0;

        if ($categoriaId > 0) {
            $sku = (new Produto())->gerarProximoSKU($categoriaId);
            echo $sku;
        } else {
            echo "Selecione a categoria";
        }
        exit; // Impede que o layout do sistema seja carregado
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . "/../Views/{$view}.php";
        require __DIR__ . '/../Views/layout/footer.php';
    }
}
