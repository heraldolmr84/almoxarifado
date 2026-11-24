<?php
// public/index.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

session_start();

// Carrega o autoload do Composer
require __DIR__ . '/../vendor/autoload.php';

// Carrega as variáveis de ambiente do arquivo .env
// Carrega as variáveis de ambiente usando o leitor nativo
App\Core\Env::carregar(__DIR__ . '/../.env');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['_csrf_token'] ?? '';

    if (!\App\Core\Csrf::validar($token)) {
        die("Erro de segurança: Token CSRF inválido ou expirado. Volte e tente novamente.");
    }
}



use App\Core\Router;
use App\Core\Auth;

// Pega a controller e action da URL (ou define o padrão)
$controller = $_GET['controller'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

// Trava de segurança de Login
if (!Auth::check() && ($_GET['controller'] ?? 'auth') !== 'auth') {
    header('Location: ?controller=auth&action=login');
    exit;
}

// Trava de troca de senha
if (Auth::check()) {
    $user = Auth::user();
    if (($user['deve_trocar_senha'] ?? false) && ($_GET['controller'] ?? '') !== 'auth') {
        header('Location: ?controller=auth&action=trocarSenha');
        exit;
    }
}

// ==========================================================
// LÓGICA DE ROTA PADRÃO (Se o usuário digitar apenas a URL base)
// ==========================================================
$defaultController = 'dashboard';
$defaultAction = 'index';

// Se estiver logado, define a página inicial baseada no perfil
if (Auth::check()) {
    $user = Auth::user();
    // Operador não tem acesso ao Dashboard, então cai direto nas requisições
    if ($user['perfil'] === 'OPERADOR') {
        $defaultController = 'requisicao';
        $defaultAction = 'list';
    } elseif ($user['perfil'] === 'GERENTE') {
        // Gerente cai direto no painel estratégico
        $defaultController = 'dashboard';
        $defaultAction = 'gerencial';
    }

} else {
    // Se não estiver logado, a tela padrão é o login
    $defaultController = 'auth';
    $defaultAction = 'login';
}

// Pega o que veio na URL, ou usa o padrão definido acima
$controller = $_GET['controller'] ?? $defaultController;
$action = $_GET['action'] ?? $defaultAction;

// Caso especial: Se a URL for ?controller=dashboard mas não tiver action, força 'index'
if ($controller === 'dashboard' && !isset($_GET['action'])) {
    $action = 'index';
}

$_GET['controller'] = $controller;
$_GET['action'] = $action;
// ==========================================================

$router = new Router();
$router->dispatch($_GET);