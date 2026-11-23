<?php
namespace App\Controllers;

use App\Models\Dashboard;
use App\Core\Auth;

class DashboardController
{

    // Construtor inicial
    public function __construct()
    {
        $user = Auth::user();
        // Se for Operador, manda de volta para a tela de requisições
        if ($user['perfil'] === 'OPERADOR') {
            header('Location: ?controller=requisicao&action=list');
            exit;
        }
    }
    public function indexAction(): void
    {
        $model = new Dashboard();
        $permissoes = Auth::getAlmoxarifadosPermitidos();
        $limit = 5; // 5 itens por página

        // Paginação do Estoque Baixo
        $pageEstoque = isset($_GET['page_estoque']) && is_numeric($_GET['page_estoque']) ? (int) $_GET['page_estoque'] : 1;
        if ($pageEstoque < 1)
            $pageEstoque = 1;
        $offsetEstoque = ($pageEstoque - 1) * $limit;

        $estoqueBaixo = $model->getProdutosEstoqueBaixo($permissoes, $limit, $offsetEstoque);
        $totalEstoque = $model->countProdutosEstoqueBaixo($permissoes);
        $totalPaginasEstoque = ceil($totalEstoque / $limit);

        // Paginação dos Lotes a Vencer
        $pageLotes = isset($_GET['page_lotes']) && is_numeric($_GET['page_lotes']) ? (int) $_GET['page_lotes'] : 1;
        if ($pageLotes < 1)
            $pageLotes = 1;
        $offsetLotes = ($pageLotes - 1) * $limit;

        $lotesVencendo = $model->getLotesAVencer($limit, $offsetLotes);
        $totalLotes = $model->countLotesAVencer();
        $totalPaginasLotes = ceil($totalLotes / $limit);

        $this->render('dashboard/index', [
            'totalProdutos' => $model->getTotalProdutos(),
            'reqPendentes' => $model->getRequisicoesPendentes($permissoes),
            'reqAprovadas' => $model->getRequisicoesAprovadas($permissoes),
            'totalBackorders' => $model->getTotalBackorders($permissoes),
            'estoqueBaixo' => $estoqueBaixo,
            'lotesVencendo' => $lotesVencendo,

            // Passando dados da paginação para a View
            'pageEstoque' => $pageEstoque,
            'totalPaginasEstoque' => $totalPaginasEstoque,
            'pageLotes' => $pageLotes,
            'totalPaginasLotes' => $totalPaginasLotes
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

    public function apiStatusAction(): void
    {
        // Limpa qualquer buffer para não sujar o JSON
        ob_clean();

        $model = new \App\Models\Dashboard();
        $permissoes = \App\Core\Auth::getAlmoxarifadosPermitidos();

        // Pega os dados atualizados
        $dados = [
            'reqPendentes' => $model->getRequisicoesPendentes($permissoes),
            'reqAprovadas' => $model->getRequisicoesAprovadas($permissoes),
            'totalBackorders' => $model->getTotalBackorders($permissoes)
        ];

        header('Content-Type: application/json');
        echo json_encode($dados);
        exit;
    }

    public function gerencialAction(): void
    {
        // Segurança extra
        $user = \App\Core\Auth::user();
        if (!in_array($user['perfil'], ['ADMIN', 'GERENTE'])) {
            header('Location: ?controller=dashboard&action=index');
            exit;
        }

        $model = new Dashboard();

        $this->render('dashboard/gerencial', [
            'totalGastoMes' => $model->getTotalGastoMes(),
            'graficoMeses' => $model->getComprasUltimosMeses(),
            'graficoDepartamentos' => $model->getConsumoPorDepartamento(),
            'totalProdutos' => $model->getTotalProdutos()
        ]);
    }
}