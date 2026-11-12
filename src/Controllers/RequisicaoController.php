<?php
namespace App\Controllers;

use App\Models\Requisicao;
use App\Core\Auth;

class RequisicaoController
{
    public function listAction(): void
    {
        $user = Auth::user();
        $model = new Requisicao();

        // Pega os filtros da URL, se existirem
        $filtros = [
            'status' => $_GET['status'] ?? '',
            'numero' => $_GET['numero'] ?? '',
            'departamento_id' => $_GET['departamento_id'] ?? ''
        ];

        $requisicoes = $model->listarRecentes($user['id'], $user['perfil'], $filtros);
        $mensagem = $_SESSION['mensagem'] ?? null;
        unset($_SESSION['mensagem']);

        $this->render('requisicoes/list', [
            'requisicoes' => $requisicoes,
            'mensagem' => $mensagem,
            'filtros' => $filtros,
            'departamentos' => $model->getDepartamentos() // Para preencher o select
        ]);
    }

    public function createAction(): void
    {
        $model = new Requisicao();
        $user = Auth::user(); // Pega os dados da sessão

        // 1. Descobre o departamento do usuário logado
        $departamentoId = $user['departamento_id'] ?? 0;

        if (!$departamentoId) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Você não possui um departamento vinculado. Contate o administrador.'];
            header('Location: ?controller=requisicao&action=list');
            exit;
        }

        // 2. Busca os almoxarifados que o departamento dele tem acesso
        $almoxarifadosDisponiveis = $model->getAlmoxarifadosPorDepartamento($departamentoId);

        // 3. Captura qual almoxarifado ele escolheu na tela
        $almoxarifadoId = isset($_GET['almoxarifado_id']) ? (int) $_GET['almoxarifado_id'] : 0;

        // Se ele tem acesso a apenas um almoxarifado, seleciona ele automaticamente
        if (count($almoxarifadosDisponiveis) === 1) {
            $almoxarifadoId = $almoxarifadosDisponiveis[0]['id'];
        }

        // 4. Busca os produtos (só se ele já escolheu o almoxarifado)
        $produtosDisponiveis = [];
        if ($almoxarifadoId > 0) {
            $produtosDisponiveis = $model->getProdutos($almoxarifadoId);
        }

        $this->render('requisicoes/form', [
            'almoxarifadosDisponiveis' => $almoxarifadosDisponiveis,
            'produtosDisponiveis' => $produtosDisponiveis,
            'departamentoSelecionado' => $departamentoId,
            'almoxarifadoSelecionado' => $almoxarifadoId,
            'mensagem' => $_SESSION['mensagem'] ?? null
        ]);
        unset($_SESSION['mensagem']);
    }

    public function storeAction(): void
    {
        // 1. Pegamos os dados do usuário logado ANTES da validação
        $user = Auth::user();

        // 2. Injetamos o departamento do usuário no $_POST
        // (Se o usuário for Admin e não tiver departamento, fica nulo)
        $_POST['departamento_id'] = $user['departamento_id'] ?? null;

        // 3. Validação (agora o departamento estará preenchido se o usuário tiver um)
        if (empty($_POST['departamento_id']) || empty($_POST['almoxarifado_id']) || empty($_POST['justificativa'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro: Verifique se você possui um departamento vinculado e preencha todos os campos.'];
            header('Location: ?controller=requisicao&action=create');
            exit;
        }

        try {
            // 4. Salva no banco (o $_POST já vai com o departamento_id correto)
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
            header('Location: ?controller=requisicao&action=list');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $motivo = $_POST['motivo'] ?? 'Sem motivo informado.';

        (new Requisicao())->rejeitar($id, $user['id'], $motivo);
        $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Requisição Rejeitada.'];
        header('Location: ?controller=requisicao&action=show&id=' . $id);
        exit;
    }

    //FUNÇÃO PARA ATENDER A REQUISIÇÃO (APROVADA -> ATENDIDA)
    public function atenderAction(array $query): void
    {
        $user = Auth::user();
        if (!in_array($user['perfil'], ['ADMIN', 'ALMOXARIFE'])) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Apenas almoxarifes podem atender requisições.'];
            header('Location: ?controller=requisicao&action=show&id=' . $query['id']);
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $quantidades = $_POST['quantidade_atendida'] ?? []; // Array com ID do item => Qtd

        try {
            (new Requisicao())->atender($id, $quantidades, $user['id']);
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Requisição atendida com sucesso! Estoque atualizado.'];
        } catch (\Exception $e) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => $e->getMessage()];
        }

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

    public function backordersAction(): void
    {
        $backorders = (new Requisicao())->listarBackorders();
        $this->render('requisicoes/backorders', ['backorders' => $backorders]);
    }

    // Função para Cancelar uma requisição (apenas ADMIN ou ALMOXARIFE podem cancelar)
    public function cancelarAction(): void
    {
        $user = Auth::user();
        $id = (int) ($_POST['id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        $requisicao = (new Requisicao())->find($id);

        // Validação de permissão
        if (!$requisicao) {
            header('Location: ?controller=requisicao&action=list');
            exit;
        }

        // Operador só cancela as próprias requisições se ainda forem rascunho ou aguardando aprovação
        if ($user['perfil'] === 'OPERADOR') {
            if ($requisicao['solicitante_id'] != $user['id'] || !in_array($requisicao['status'], ['RASCUNHO', 'AGUARDANDO_APROVACAO'])) {
                $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Você não tem permissão para cancelar esta requisição.'];
                header('Location: ?controller=requisicao&action=show&id=' . $id);
                exit;
            }
        }

        if (empty($motivo)) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Você precisa informar o motivo do cancelamento.'];
            header('Location: ?controller=requisicao&action=show&id=' . $id);
            exit;
        }

        try {
            (new Requisicao())->cancelar($id, $motivo, $user['id']);
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Requisição cancelada com sucesso! Estoque (se havia sido entregue) devolvido.'];
        } catch (\Exception $e) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => $e->getMessage()];
        }

        header('Location: ?controller=requisicao&action=show&id=' . $id);
        exit;
    }

    // Função para gerar o PDF da requisição
    public function printAction(array $query): void
    {
        $id = (int) ($query['id'] ?? 0);
        $requisicao = (new Requisicao())->find($id);

        if (!$requisicao) {
            header('Location: ?controller=requisicao&action=list');
            exit;
        }

        // Renderização customizada (sem o header e footer padrão do sistema)
        extract(['requisicao' => $requisicao]);
        require __DIR__ . '/../Views/requisicoes/print.php';
    }
}