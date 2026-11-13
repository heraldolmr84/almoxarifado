<?php
// public/index.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Carrega o autoload do Composer
require __DIR__ . '/../vendor/autoload.php';

// Carrega as variáveis de ambiente do arquivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

session_start();

use App\Core\Router;
use App\Core\Auth;

// Pega a controller e action da URL (ou define o padrão)
$controller = $_GET['controller'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

// Se NÃO estiver logado E NÃO for a tela de auth, joga pro login
if (!Auth::check() && $controller !== 'auth') {
    header('Location: ?controller=auth&action=login');
    exit;
}

if (Auth::check()) {
    $user = Auth::user();

    $defaultController = ($user['perfil'] === 'OPERADOR') ? 'requisicao' : 'dashboard';

    // Se precisa trocar senha, só deixa acessar a tela de troca ou logout
    if ($user['deve_trocar_senha'] && $controller !== 'auth') {
        header('Location: ?controller=auth&action=trocarSenha');
        exit;
    }
} else {
    $defaultController = 'auth';
}

$controller = $_GET['controller'] ?? $defaultController;
$action = $_GET['action'] ?? (($controller === 'auth') ? 'login' : 'list');

// Se for Dashboard, a action padrão é index, não list
if ($controller === 'dashboard' && !isset($_GET['action'])) {
    $action = 'index';
}

// Se NÃO estiver logado E NÃO estiver tentando acessar a tela de login, bloqueia!
if (!Auth::check() && $controller !== 'auth') {
    header('Location: ?controller=auth&action=login');
    exit;
}

$router = new Router();
$router->dispatch($_GET);