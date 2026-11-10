<?php
// public/index.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Auth;

// Pega a controller e action da URL (ou define o padrão)
$controller = $_GET['controller'] ?? 'produto';
$action = $_GET['action'] ?? 'list';

// Se NÃO estiver logado E NÃO estiver tentando acessar a tela de login, bloqueia!
if (!Auth::check() && $controller !== 'auth') {
    header('Location: ?controller=auth&action=login');
    exit;
}

$router = new Router();
$router->dispatch($_GET);