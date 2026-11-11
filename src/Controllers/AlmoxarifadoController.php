<?php
namespace App\Controllers;

use App\Models\Almoxarifado;
use App\Core\Auth;

class AlmoxarifadoController
{
    public function __construct() {
        if (Auth::user()['perfil'] !== 'ADMIN') { die("Acesso negado."); }
    }

    public function listAction(): void {
        $almoxarifados = (new Almoxarifado())->all();
        $mensagem = $_SESSION['mensagem'] ?? null; unset($_SESSION['mensagem']);
        $this->render('almoxarifados/list', ['almoxarifados' => $almoxarifados, 'mensagem' => $mensagem]);
    }
    public function createAction(): void { $this->render('almoxarifados/form'); }
    
    public function storeAction(): void {
        try {
            (new Almoxarifado())->create($_POST);
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Almoxarifado cadastrado!'];
            header('Location: ?controller=almoxarifado&action=list');
        } catch (\Exception $e) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao cadastrar.'];
            header('Location: ?controller=almoxarifado&action=create');
        }
        exit;
    }
    
    public function editAction(array $query): void {
        $almox = (new Almoxarifado())->find((int) $query['id']);
        if (!$almox) { header('Location: ?controller=almoxarifado&action=list'); exit; }
        $this->render('almoxarifados/form', ['almoxarifado' => $almox]);
    }
    
    public function updateAction(): void {
        (new Almoxarifado())->update((int) $_POST['id'], $_POST);
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Almoxarifado atualizado!'];
        header('Location: ?controller=almoxarifado&action=list'); exit;
    }
    
    public function deleteAction(array $query): void {
        (new Almoxarifado())->delete((int) $query['id']);
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Almoxarifado desativado!'];
        header('Location: ?controller=almoxarifado&action=list'); exit;
    }

    private function render(string $view, array $data = []): void {
        extract($data);
        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . "/../Views/{$view}.php";
        require __DIR__ . '/../Views/layout/footer.php';
    }
}