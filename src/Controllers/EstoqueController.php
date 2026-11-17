<?php
namespace App\Controllers;

use App\Models\Estoque;
use App\Core\Auth;

class EstoqueController
{
    public function __construct()
    {
        $user = Auth::user();
        if (!in_array($user['perfil'], ['ADMIN', 'ALMOXARIFE', 'GERENTE'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Acesso negado.'];
            header('Location: ?controller=requisicao&action=list');
            exit;
        }
    }

    public function listAction(): void
    {
        $model = new Estoque();
        $permissoes = Auth::getAlmoxarifadosPermitidos();
        
        $filtros = [
            'codigo' => $_GET['codigo'] ?? '',
            'descricao' => $_GET['descricao'] ?? '',
            'almoxarifado_id' => $_GET['almoxarifado_id'] ?? ''
        ];

        $limit = 8;
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        $estoque = $model->getEstoqueAtual($filtros, $permissoes, $limit, $offset);
        $totalRegistros = $model->countEstoque($filtros, $permissoes);
        $totalPaginas = ceil($totalRegistros / $limit);
        
        $this->render('estoque/list', [
            'estoque' => $estoque,
            'filtros' => $filtros,
            'almoxarifados' => $model->getAlmoxarifadosPermitidos(),
            'page' => $page,
            'totalPaginas' => $totalPaginas,
            'totalRegistros' => $totalRegistros
        ]);
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . "/../Views/{$view}.php";
        require __DIR__ . '/../Views/layout/footer.php';
    }
}