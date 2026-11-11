<?php
namespace App\Controllers;

use App\Models\Dashboard;

class DashboardController
{
    public function indexAction(): void
    {
        $model = new Dashboard();
        
        $this->render('dashboard/index', [
            'totalProdutos' => $model->getTotalProdutos(),
            'reqPendentes'  => $model->getRequisicoesPendentes(),
            'reqAprovadas'  => $model->getRequisicoesAprovadas(),
            'estoqueBaixo'  => $model->getProdutosEstoqueBaixo()
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