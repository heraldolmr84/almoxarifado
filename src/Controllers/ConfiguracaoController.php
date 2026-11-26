<?php
namespace App\Controllers;

use App\Models\Configuracao;
use App\Core\Auth;

class ConfiguracaoController
{
    public function __construct()
    {
        if (Auth::user()['perfil'] !== 'ADMIN') {
            die("Acesso negado.");
        }
    }

    public function editAction(): void
    {
        $config = (new Configuracao())->get();
        $mensagem = $_SESSION['mensagem'] ?? null; unset($_SESSION['mensagem']);
        
        $this->render('configuracoes/form', ['config' => $config, 'mensagem' => $mensagem]);
    }

    public function updateAction(): void
    {
        $dados = [
            'razao_social' => $_POST['razao_social'],
            'nome_fantasia' => $_POST['nome_fantasia'],
            'cnpj' => preg_replace('/[^0-9]/', '', $_POST['cnpj']),
            'cor_primaria' => $_POST['cor_primaria'],
            'logo_path' => null
        ];

        // Processamento do Upload da Logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $extensao = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'svg'];

            if (in_array($extensao, $extensoesPermitidas)) {
                // Gera um nome único para não sobrescrever arquivos
                $novoNome = 'logo_' . uniqid() . '.' . $extensao;
                $destino = __DIR__ . '/../../public/uploads/logos/' . $novoNome;

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $destino)) {
                    $dados['logo_path'] = 'uploads/logos/' . $novoNome;
                }
            }
        }

        (new Configuracao())->update($dados);
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Configurações atualizadas com sucesso!'];
        header('Location: ?controller=configuracao&action=edit');
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