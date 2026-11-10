<?php
namespace App\Controllers;

use App\Models\Movimentacao;
use App\Core\Auth;

class MovimentacaoController
{
    public function listAction(): void
    {
        $movimentacoes = (new Movimentacao())->listarRecentes();
        $mensagem = $_SESSION['mensagem'] ?? null;
        unset($_SESSION['mensagem']);
        
        $this->render('movimentacoes/list', ['movimentacoes' => $movimentacoes, 'mensagem' => $mensagem]);
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

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . "/../Views/{$view}.php";
        require __DIR__ . '/../Views/layout/footer.php';
    }
}