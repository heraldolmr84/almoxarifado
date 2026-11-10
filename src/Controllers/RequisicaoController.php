<?php
namespace App\Controllers;

use App\Models\Requisicao;
use App\Core\Auth;

class RequisicaoController
{
    public function listAction(): void
    {
        $user = Auth::user();
        $requisicoes = (new Requisicao())->listarRecentes($user['id'], $user['perfil']);
        $mensagem = $_SESSION['mensagem'] ?? null;
        unset($_SESSION['mensagem']);
        
        $this->render('requisicoes/list', ['requisicoes' => $requisicoes, 'mensagem' => $mensagem]);
    }

    public function createAction(): void
    {
        $model = new Requisicao();
        $mensagem = $_SESSION['mensagem'] ?? null;
        unset($_SESSION['mensagem']);

        $this->render('requisicoes/form', [
            'departamentos' => $model->getDepartamentos(),
            'almoxarifados' => $model->getAlmoxarifados(),
            'produtos' => $model->getProdutos(),
            'mensagem' => $mensagem
        ]);
    }

    public function storeAction(): void
    {
        if (empty($_POST['departamento_id']) || empty($_POST['almoxarifado_id']) || empty($_POST['justificativa'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Preencha o cabeçalho da requisição.'];
            header('Location: ?controller=requisicao&action=create');
            exit;
        }

        try {
            $user = Auth::user();
            (new Requisicao())->create($_POST, $user['id']);
            
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Requisição criada como Rascunho! Envie-a para aprovação.'];
            header('Location: ?controller=requisicao&action=list');
        } catch (\Exception $e) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao criar requisição: ' . $e->getMessage()];
            header('Location: ?controller=requisicao&action=create');
        }
        exit;
    }

    //açoes de requisição: aprovar, rejeitar, atender
        public function showAction(array $query): void
    {
        $id = (int) ($query['id'] ?? 0);
        $requisicao = (new Requisicao())->find($id);

        if (!$requisicao) {
            header('Location: ?controller=requisicao&action=list');
            exit;
        }

        $mensagem = $_SESSION['mensagem'] ?? null;
        unset($_SESSION['mensagem']);

        $this->render('requisicoes/show', [
            'requisicao' => $requisicao,
            'mensagem' => $mensagem
        ]);
    }

    public function enviarAction(array $query): void
    {
        $id = (int) ($query['id'] ?? 0);
        (new Requisicao())->enviarParaAprovacao($id);
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Requisição enviada para aprovação!'];
        header('Location: ?controller=requisicao&action=show&id=' . $id);
        exit;
    }

    public function aprovarAction(array $query): void
    {
        $user = Auth::user();
        // Apenas Admin e Almoxarife podem aprovar
        if (!in_array($user['perfil'], ['ADMIN', 'ALMOXARIFE'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Você não tem permissão para aprovar.'];
            header('Location: ?controller=requisicao&action=show&id=' . $query['id']);
            exit;
        }

        $id = (int) ($query['id'] ?? 0);
        (new Requisicao())->aprovar($id, $user['id']);
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Requisição Aprovada com sucesso!'];
        header('Location: ?controller=requisicao&action=show&id=' . $id);
        exit;
    }

    public function rejeitarAction(): void
    {
        $user = Auth::user();
        if (!in_array($user['perfil'], ['ADMIN', 'ALMOXARIFE'])) {
            header('Location: ?controller=requisicao&action=list'); exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $motivo = $_POST['motivo'] ?? 'Sem motivo informado.';
        
        (new Requisicao())->rejeitar($id, $user['id'], $motivo);
        $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Requisição Rejeitada.'];
        header('Location: ?controller=requisicao&action=show&id=' . $id);
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