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
        
        $filtros = [
            'tipo' => $_GET['tipo'] ?? '',
            'documento' => $_GET['documento'] ?? '',
            'data_inicio' => $_GET['data_inicio'] ?? '',
            'data_fim' => $_GET['data_fim'] ?? ''
        ];

        $movimentacoes = $model->listarRecentes($filtros);
        $mensagem = $_SESSION['mensagem'] ?? null;
        unset($_SESSION['mensagem']);
        
        $this->render('movimentacoes/list', [
            'movimentacoes' => $movimentacoes, 
            'mensagem' => $mensagem,
            'filtros' => $filtros
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

        $mensagem = $_SESSION['mensagem'] ?? null;
        unset($_SESSION['mensagem']);
        
        $this->render('movimentacoes/saida', [
            'produtos' => $model->getProdutos(),
            'almoxarifados' => $model->getAlmoxarifados(),
            'mensagem' => $mensagem
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
}