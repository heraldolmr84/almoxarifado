<?php 
use App\Core\Auth;
$user = Auth::user();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Almoxarifado</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="?controller=produto&action=list">Almoxarifado</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <?php if (in_array($user['perfil'], ['ADMIN', 'ALMOXARIFE'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="?controller=dashboard&action=index">Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?controller=produto&action=list">Produtos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?controller=categoria&action=list">Categorias</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?controller=unidade&action=list">Unidades</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?controller=movimentacao&action=list">Movimentações</a>
                    </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a class="nav-link" href="?controller=requisicao&action=list">Requisições</a>
                </li>

                <?php if ($user['perfil'] === 'ADMIN'): ?>
                    <li class="nav-item"><a class="nav-link" href="?controller=almoxarifado&action=list">Almoxarifados</a></li>
                    <li class="nav-item"><a class="nav-link" href="?controller=usuario&action=list">Usuários</a></li>
                <?php endif; ?>
            </ul>
            <div class="ms-auto d-flex align-items-center">
                <?php if ($user): ?>
                <span class="navbar-text text-light me-3">
                    Olá, <strong><?= htmlspecialchars($user['nome']) ?></strong> (<?= $user['perfil'] ?>)
                </span>
                <a href="?controller=auth&action=logout" class="btn btn-outline-light btn-sm">Sair</a>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</nav>
<main class="container">