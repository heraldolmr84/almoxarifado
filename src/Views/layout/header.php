<?php
use App\Core\Auth;
$user = Auth::user();
?>



<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/x-icon" href="assets/img/favicom.png">

    <title>Sistema de Almoxarifado - FACOM</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
</head>

<body class="d-flex flex-column min-vh-100">

    <?php if ($user): ?>
        <nav class="navbar navbar-expand-lg  mb-4">
            <div class="container"> <!-- Usando fluid para esticar bem a tela -->
                <a class="navbar-brand d-flex align-items-center logo-img" href="?controller=dashboard&action=index">
                    <img src="assets/img/logo_facom.png" alt="UFMS Almoxarifado" class="logo">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">

                        <!-- Produtos e Movimentações (Admin e Almoxarife) -->
                        <?php if (in_array($user['perfil'], ['ADMIN', 'ALMOXARIFE'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="?controller=dashboard&action=index">Início</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="?controller=produto&action=list">Produtos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="?controller=movimentacao&action=list">Movimentações</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="?controller=requisicao&action=backorders">Backorders</a>
                            </li>
                        <?php endif; ?>

                        <!-- Requisições (Todos) -->
                        <li class="nav-item">
                            <a class="nav-link" href="?controller=requisicao&action=list">Requisições</a>
                        </li>


                        <!-- MENU DROPDOWN: ADMINISTRAÇÃO -->
                        <?php if (in_array($user['perfil'], ['ADMIN', 'ALMOXARIFE'])): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    Administração
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="?controller=categoria&action=list">Categorias</a></li>
                                    <li><a class="dropdown-item" href="?controller=unidade&action=list">Unidades de Medida</a>
                                    </li>
                                    <li><a class="dropdown-item" href="?controller=fornecedor&action=list">Fornecedores</a></li>
                                    <li><a class="dropdown-item" href="?controller=almoxarifado&action=list">Almoxarifados</a>
                                    </li>

                                    <!-- Usuários: Apenas Admin -->
                                    <?php if ($user['perfil'] === 'ADMIN'): ?>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item" href="?controller=usuario&action=list">Usuários</a></li>
                                        <li><a class="dropdown-item" href="?controller=departamento&action=list">Departamentos</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <!-- Área do Usuário Logado (Alinhado à direita) -->
                    <?php if ($user): ?>
                        <div class="d-flex align-items-center">
                            <span class="navbar-text text-light me-3">
                                Olá, <strong>
                                    <?= htmlspecialchars($user['nome']) ?>
                                </strong> (
                                <?= $user['perfil'] ?>)
                            </span>
                            <a href="?controller=auth&action=logout" class="btn btn-outline-light btn-sm">Sair</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    <?php endif; ?>
    <main class="container flex-grow-1 mb-4">