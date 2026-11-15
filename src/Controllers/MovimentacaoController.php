<?php
namespace App\Controllers;

use App\Models\Movimentacao;
use App\Core\Auth;

class MovimentacaoController
{

    public function __construct()
    {
        $user = \App\Core\Auth::user();
        if ($user['perfil'] === 'OPERADOR') {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Acesso negado. Apenas almoxarifes podem movimentar o estoque diretamente.'];
            header('Location: ?controller=requisicao&action=list');
            exit;
        }
    }

    public function listAction(): void
    {
        $model = new Movimentacao();
        $permissoes = Auth::getAlmoxarifadosPermitidos();

        $filtros = [
            'tipo' => $_GET['tipo'] ?? '',
            'documento' => $_GET['documento'] ?? '',
            'data_inicio' => $_GET['data_inicio'] ?? '',
            'data_fim' => $_GET['data_fim'] ?? ''
        ];

        // Lógica de Paginação
        $limit = 10;
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($page < 1)
            $page = 1;
        $offset = ($page - 1) * $limit;

        $movimentacoes = $model->listarRecentes($filtros, $permissoes, $limit, $offset);
        $totalRegistros = $model->countAll($filtros, $permissoes);
        $totalPaginas = ceil($totalRegistros / $limit);

        $mensagem = $_SESSION['mensagem'] ?? null;
        unset($_SESSION['mensagem']);

        $this->render('movimentacoes/list', [
            'movimentacoes' => $movimentacoes,
            'mensagem' => $mensagem,
            'filtros' => $filtros,
            'page' => $page,
            'totalPaginas' => $totalPaginas,
            'totalRegistros' => $totalRegistros
        ]);
    }

    public function entradaAction(): void
    {
        $model = new Movimentacao();

        $mensagem = $_SESSION['mensagem'] ?? null;
        unset($_SESSION['mensagem']);

        $this->render('movimentacoes/entrada', [
            'produtos' => $model->getProdutos(),
            'almoxarifados' => $model->getAlmoxarifados(),
            'mensagem' => $mensagem
        ]);
    }

    public function storeEntradaAction(): void
    {
        if (empty($_POST['produto_id']) || empty($_POST['almoxarifado_id']) || empty($_POST['quantidade']) || $_POST['quantidade'] <= 0) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Preencha os campos obrigatórios e uma quantidade válida.'];
            header('Location: ?controller=movimentacao&action=entrada');
            exit;
        }

        try {
            $usuarioId = Auth::user()['id'];
            (new Movimentacao())->registrarEntrada($_POST, $usuarioId);

            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Entrada de estoque registrada com sucesso!'];
            header('Location: ?controller=movimentacao&action=list');
        } catch (\Exception $e) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao registrar entrada: ' . $e->getMessage()];
            header('Location: ?controller=movimentacao&action=entrada');
        }
        exit;
    }

    public function saidaAction(): void
    {
        $model = new Movimentacao();
        $produtoId = isset($_GET['produto_id']) ? (int) $_GET['produto_id'] : 0;
        $almoxarifadoId = isset($_GET['almoxarifado_id']) ? (int) $_GET['almoxarifado_id'] : 0;

        $lotes = [];
        if ($produtoId > 0 && $almoxarifadoId > 0) {
            $lotes = $model->getLotesDisponiveis($produtoId, $almoxarifadoId);
        }

        $this->render('movimentacoes/saida', [
            'produtos' => $model->getProdutos(),
            'almoxarifados' => $model->getAlmoxarifados(),
            'lotes' => $lotes,
            'produtoSelecionado' => $produtoId,
            'almoxarifadoSelecionado' => $almoxarifadoId
        ]);
    }

    public function storeSaidaAction(): void
    {
        if (empty($_POST['produto_id']) || empty($_POST['almoxarifado_id']) || empty($_POST['quantidade']) || $_POST['quantidade'] <= 0) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Preencha os campos obrigatórios e uma quantidade válida.'];
            header('Location: ?controller=movimentacao&action=saida');
            exit;
        }

        try {
            $usuarioId = Auth::user()['id'];
            (new Movimentacao())->registrarSaida($_POST, $usuarioId);

            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Saída de estoque registrada com sucesso!'];
            header('Location: ?controller=movimentacao&action=list');
        } catch (\Exception $e) {
            // Se o saldo for insuficiente, a Exception cai aqui e mostramos a mensagem
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => $e->getMessage()];
            header('Location: ?controller=movimentacao&action=saida');
        }
        exit;
    }

    // Método para mostrar a View de detalhes de uma movimentação específica
    public function showAction(array $query): void
    {
        $id = (int) ($query['id'] ?? 0);
        $movimentacao = (new Movimentacao())->find($id);

        if (!$movimentacao) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Movimentação não encontrada.'];
            header('Location: ?controller=movimentacao&action=list');
            exit;
        }

        $this->render('movimentacoes/show', ['mov' => $movimentacao]);
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . "/../Views/{$view}.php";
        require __DIR__ . '/../Views/layout/footer.php';
    }

    // Função para criar a ação de transferência de estoque entre almoxarifados
    public function transferenciaAction(): void
    {
        $model = new Movimentacao();
        $mensagem = $_SESSION['mensagem'] ?? null;
        unset($_SESSION['mensagem']);

        $this->render('movimentacoes/transferencia', [
            'produtos' => $model->getProdutos(),
            'almoxarifados' => $model->getAlmoxarifados(),
            'mensagem' => $mensagem
        ]);
    }

    public function storeTransferenciaAction(): void
    {
        if (empty($_POST['produto_id']) || empty($_POST['almoxarifado_origem_id']) || empty($_POST['almoxarifado_destino_id']) || empty($_POST['quantidade']) || $_POST['quantidade'] <= 0) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Preencha todos os campos corretamente.'];
            header('Location: ?controller=movimentacao&action=transferencia');
            exit;
        }

        try {
            $usuarioId = Auth::user()['id'];
            (new Movimentacao())->registrarTransferencia($_POST, $usuarioId);

            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Transferência realizada com sucesso!'];
            header('Location: ?controller=movimentacao&action=list');
        } catch (\Exception $e) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => $e->getMessage()];
            header('Location: ?controller=movimentacao&action=transferencia');
        }
        exit;
    }
}