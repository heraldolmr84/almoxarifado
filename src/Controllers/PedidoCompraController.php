<?php
namespace App\Controllers;

use App\Models\PedidoCompra;
use App\Models\Produto;
use App\Core\Auth;

class PedidoCompraController
{
    public function listAction(): void
    {
        $user = Auth::user();
        $db = \App\Core\Database::getConnection();

        $sql = "SELECT p.*, u.nome AS solicitante FROM pedidos_compra p JOIN usuarios u ON u.id = p.solicitante_id";

        // Se for Gerente, não vê os rascunhos (só o que foi enviado para frente)
        if ($user['perfil'] === 'GERENTE') {
            $sql .= " WHERE p.status != 'RASCUNHO'";
        }

        $sql .= " ORDER BY p.id DESC";
        $pedidos = $db->query($sql)->fetchAll();

        $mensagem = $_SESSION['mensagem'] ?? null;
        unset($_SESSION['mensagem']);
        $this->render('compras/list', ['pedidos' => $pedidos, 'mensagem' => $mensagem]);
    }

    public function createAction(): void
    {
        $user = Auth::user();
        if ($user['perfil'] === 'GERENTE') {
            header('Location: ?controller=pedidoCompra&action=list'); exit;
        }
        
        $this->render('compras/form', [
            'produtos' => (new Produto())->getProdutosParaCompra(),
            'mensagem' => $_SESSION['mensagem'] ?? null
        ]);
        unset($_SESSION['mensagem']);
    }

    public function storeAction(): void
    {
        if (empty($_POST['justificativa'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'A justificativa é obrigatória.'];
            header('Location: ?controller=pedidoCompra&action=create');
            exit;
        }

        try {
            $user = Auth::user();
            (new PedidoCompra())->create($_POST, $user['id']);
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Pedido de compra criado como Rascunho!'];
            header('Location: ?controller=pedidoCompra&action=list');
        } catch (\Exception $e) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao criar: ' . $e->getMessage()];
            header('Location: ?controller=pedidoCompra&action=create');
        }
        exit;
    }

    public function showAction(array $query): void
    {
        $id = (int) ($query['id'] ?? 0);
        $pedido = (new PedidoCompra())->find($id);

        // Se o pedido não existir, volta para a lista
        if (!$pedido) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Pedido de compra não encontrado.'];
            header('Location: ?controller=pedidoCompra&action=list');
            exit;
        }

        $mensagem = $_SESSION['mensagem'] ?? null;
        unset($_SESSION['mensagem']);

        // Passa a variável $pedido para a view
        $this->render('compras/show', ['pedido' => $pedido, 'mensagem' => $mensagem]);
    }

    public function enviarAction(array $query): void
    {
        (new PedidoCompra())->mudarStatus((int) $query['id'], 'AGUARDANDO_APROVACAO');
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Pedido enviado para aprovação!'];
        header('Location: ?controller=pedidoCompra&action=show&id=' . $query['id']);
        exit;
    }

    public function aprovarAction(array $query): void
    {
        $user = Auth::user();
        // O Almoxarife foi removido daqui
        if (!in_array($user['perfil'], ['ADMIN', 'GERENTE'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Apenas a gerência pode aprovar pedidos.'];
            header('Location: ?controller=pedidoCompra&action=show&id=' . $query['id']);
            exit;
        }

        (new PedidoCompra())->mudarStatus((int) $query['id'], 'APROVADO', $user['id']);
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Pedido aprovado com sucesso!'];
        header('Location: ?controller=pedidoCompra&action=show&id=' . $query['id']);
        exit;
    }

    public function comprarAction(array $query): void
    {
        // Exibe a tela para anexar a NF
        $pedido = (new PedidoCompra())->find((int) ($query['id'] ?? 0));
        $this->render('compras/comprar', ['pedido' => $pedido]);
    }

    public function salvarCompraAction(): void
    {
        $id = (int) $_POST['id'];
        $doc = $_POST['documento_compra'] ?? null;

        (new PedidoCompra())->mudarStatus($id, 'COMPRADO', null, $doc);
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Compra registrada! Aguardando entrega.'];
        header('Location: ?controller=pedidoCompra&action=show&id=' . $id);
        exit;
    }

    public function receberAction(array $query): void
    {
        $pedido = (new PedidoCompra())->find((int) ($query['id'] ?? 0));

        if (!$pedido || !in_array($pedido['status'], ['COMPRADO', 'RECEBIDO_PARCIAL'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Pedido inválido para recebimento.'];
            header('Location: ?controller=pedidoCompra&action=list');
            exit;
        }

        $db = \App\Core\Database::getConnection();

        $this->render('compras/receber', [
            'pedido' => $pedido,
            'almoxarifados' => $db->query("SELECT id, nome FROM almoxarifados WHERE ativo = TRUE")->fetchAll(),
            'fornecedores' => $db->query("SELECT id, razao_social FROM fornecedores WHERE ativo = TRUE")->fetchAll()
        ]);
    }

    public function storeRecebimentoAction(): void
    {
        try {
            $usuarioId = Auth::user()['id'];
            (new PedidoCompra())->receber((int) $_POST['id'], $_POST, $usuarioId);

            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Recebimento registrado com sucesso! Estoque atualizado.'];
            header('Location: ?controller=pedidoCompra&action=show&id=' . $_POST['id']);
        } catch (\Exception $e) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => $e->getMessage()];
            header('Location: ?controller=pedidoCompra&action=receber&id=' . $_POST['id']);
        }
        exit;
    }

    public function printAction(array $query): void
    {
        $id = (int) ($query['id'] ?? 0);
        $pedido = (new PedidoCompra())->find($id);

        if (!$pedido) {
            header('Location: ?controller=pedidoCompra&action=list');
            exit;
        }

        // Renderização limpa (sem header/footer do sistema)
        extract(['pedido' => $pedido]);
        require __DIR__ . '/../Views/compras/print.php';
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . "/../Views/{$view}.php";
        require __DIR__ . '/../Views/layout/footer.php';
    }
}