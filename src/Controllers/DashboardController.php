<?php
namespace App\Controllers;

use App\Models\Dashboard;
use App\Core\Auth;

class DashboardController
{
    public function indexAction(): void
    {
        $model = new Dashboard();
        $permissoes = Auth::getAlmoxarifadosPermitidos();

        $this->render('dashboard/index', [
            'totalProdutos' => $model->getTotalProdutos(),
            'reqPendentes'  => $model->getRequisicoesPendentes($permissoes),
            'reqAprovadas'  => $model->getRequisicoesAprovadas($permissoes),
            'totalBackorders' => $model->getTotalBackorders($permissoes),
            'estoqueBaixo'  => $model->getProdutosEstoqueBaixo($permissoes),
            'lotesVencendo' => $model->getLotesAVencer()
        ]);
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . "/../Views/{$view}.php";
        require __DIR__ . '/../Views/layout/footer.php';
    }

    // Função para extrair as informaçções do dashboard para a .CSV
    public function exportarAction(): void
    {
        $db = \App\Core\Database::getConnection();

        // Query para buscar todo o estoque atual com os dados do produto
        $sql = "SELECT p.codigo, p.descricao, a.nome AS almoxarifado, 
                       s.quantidade, p.estoque_minimo, u.sigla AS unidade
                  FROM saldo_estoque s
                  JOIN produtos p ON p.id = s.produto_id
                  JOIN almoxarifados a ON a.id = s.almoxarifado_id
                  JOIN unidades_medida u ON u.id = p.unidade_medida_id
                 WHERE p.ativo = TRUE
                 ORDER BY p.descricao";
        $dados = $db->query($sql)->fetchAll();

        // Configura os headers para forçar o download do arquivo
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=estoque_' . date('Y-m-d') . '.csv');

        // Cria o arquivo na saída do PHP
        $output = fopen('php://output', 'w');

        // Adiciona o BOM (Byte Order Mark) para o Excel reconhecer acentos (UTF-8)
        fputs($output, "\xEF\xBB\xBF");

        // Cabeçalho do CSV
        fputcsv($output, ['Código', 'Produto', 'Almoxarifado', 'Saldo Atual', 'Estoque Mínimo', 'Unidade'], ';');

        // Linhas
        foreach ($dados as $row) {
            // O Excel entende a vírgula como separador decimal, então formatamos assim
            $linha = [
                $row['codigo'],
                $row['descricao'],
                $row['almoxarifado'],
                number_format($row['quantidade'], 3, ',', ''),
                number_format($row['estoque_minimo'], 3, ',', ''),
                $row['unidade']
            ];
            fputcsv($output, $linha, ';');
        }

        fclose($output);
        exit;
    }
}