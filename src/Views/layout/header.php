<?php
use App\Core\Auth;
$user = Auth::user();
$currentController = $_GET['controller'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/x-icon" href="assets/img/favicom.png">

    <title>SISGEM - Gestão de Materiais</title>

    <!-- Bootstrap e Ícones -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">

    <!-- Seu CSS Personalizado (certifique-se que o caminho está correto) -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <?php if ($user): ?>
        <!-- SIDEBAR LATERAL -->
        <div class="sidebar">
            <a href="?controller=dashboard&action=index"
                class="sidebar-brand d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="assets/img/logo.png" alt="Logo">
                    <span style="font-size: 1.8rem; letter-spacing: 1px;">SISGEM</span>
                </div>
                <span class="text-white-50" style="font-size: 0.7rem; font-weight: 400; margin-top: 5px;">
                    V. 1.0.1
                </span>
            </a>

            <div class="mt-3">
                <!-- GERENCIAMENTO -->
                <?php if (in_array($user['perfil'], ['ADMIN', 'ALMOXARIFE', 'GERENTE'])): ?>
                    <div class="sidebar-heading">Visão Geral</div>

                    <!-- Se for GERENTE ou ADMIN, mostra o link do Gerencial -->
                    <?php if (in_array($user['perfil'], ['ADMIN', 'GERENTE'])): ?>
                        <a href="?controller=dashboard&action=gerencial"
                            class="sidebar-link <?= ($currentController == 'dashboard' && ($_GET['action'] ?? '') == 'gerencial') ? 'active' : '' ?>">
                            <i class="bi bi-graph-up-arrow"></i> Painel Gerencial
                        </a>
                    <?php endif; ?>

                    <!-- Dashboard Operacional (Almoxarife, Admin e Gerente) -->
                    <a href="?controller=dashboard&action=index"
                        class="sidebar-link <?= ($currentController == 'dashboard' && ($_GET['action'] ?? 'index') == 'index') ? 'active' : '' ?>">
                        <i class="bi bi-speedometer2"></i> Painel Operacional
                    </a>
                <?php endif; ?>

                <!-- MOVIMENTAÇÃO -->
                <?php if (in_array($user['perfil'], ['ADMIN', 'ALMOXARIFE', 'GERENTE'])): ?>
                    <div class="sidebar-heading">Movimentação</div>
                    <a href="?controller=movimentacao&action=list"
                        class="sidebar-link <?= $currentController == 'movimentacao' ? 'active' : '' ?>">
                        <i class="bi bi-arrow-left-right"></i> Movimentações
                    </a>
                    <a href="?controller=requisicao&action=backorders"
                        class="sidebar-link <?= ($currentController == 'requisicao' && ($_GET['action'] ?? '') == 'backorders') ? 'active' : '' ?>">
                        <i class="bi bi-hourglass-split"></i> Backorders
                    </a>
                <?php endif; ?>

                <!-- REQUISIÇÕES E COMPRAS -->
                <div class="sidebar-heading">Operações</div>
                <a href="?controller=requisicao&action=list"
                    class="sidebar-link <?= ($currentController == 'requisicao' && ($_GET['action'] ?? '') != 'backorders') ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-text"></i> Requisições
                </a>

                <?php if (in_array($user['perfil'], ['ADMIN', 'ALMOXARIFE', 'GERENTE'])): ?>
                    <a href="?controller=pedidoCompra&action=list"
                        class="sidebar-link <?= $currentController == 'pedidocompra' ? 'active' : '' ?>">
                        <i class="bi bi-cart3"></i> Compras
                    </a>
                    <a href="?controller=relatorio&action=index"
                        class="sidebar-link <?= $currentController == 'relatorio' ? 'active' : '' ?>">
                        <i class="bi bi-bar-chart-line"></i> Relatórios
                    </a>
                <?php endif; ?>

                <!-- ADMINISTRAÇÃO -->
                <?php if (in_array($user['perfil'], ['ADMIN', 'ALMOXARIFE'])): ?>
                    <a class="sidebar-heading d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
                        href="#menuAdministracao" role="button" aria-expanded="true" aria-controls="menuAdministracao">
                        <span>Administração</span>
                        <i class="bi bi-chevron-down"></i>
                    </a>

                    <div class="collapse show" id="menuAdministracao">

                        <a href="?controller=categoria&action=list"
                            class="sidebar-link <?= $currentController == 'categoria' ? 'active' : '' ?>">
                            <i class="bi bi-tags"></i> Categorias
                        </a>
                        <a href="?controller=unidade&action=list"
                            class="sidebar-link <?= $currentController == 'unidade' ? 'active' : '' ?>">
                            <i class="bi bi-rulers"></i> Unidades
                        </a>
                        <a href="?controller=fornecedor&action=list"
                            class="sidebar-link <?= $currentController == 'fornecedor' ? 'active' : '' ?>">
                            <i class="bi bi-truck"></i> Fornecedores
                        </a>
                        <a href="?controller=almoxarifado&action=list"
                            class="sidebar-link <?= $currentController == 'almoxarifado' ? 'active' : '' ?>">
                            <i class="bi bi-building"></i> Almoxarifados
                        </a>

                        <?php if ($user['perfil'] === 'ADMIN'): ?>
                            <a href="?controller=usuario&action=list"
                                class="sidebar-link <?= $currentController == 'usuario' ? 'active' : '' ?>">
                                <i class="bi bi-people"></i> Usuários
                            </a>
                            <a href="?controller=departamento&action=list"
                                class="sidebar-link <?= $currentController == 'departamento' ? 'active' : '' ?>">
                                <i class="bi bi-diagram-3"></i> Departamentos
                            </a>
                        <?php endif; ?>
                    </div>

                <?php endif; ?>
            </div>
        </div>

        <!-- CONTEÚDO PRINCIPAL -->
        <div class="main-content">

            <!-- Topbar com Info do Usuário -->
            <div class="topbar">
                <span class="me-4 text-muted">
                    Olá, <strong><?= htmlspecialchars($user['nome']) ?></strong> <span
                        class="badge bg-secondary ms-1"><?= $user['perfil'] ?></span>
                </span>
                <a href="?controller=auth&action=logout" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Sair
                </a>
            </div>

        <?php else: ?>
            <!-- Se não estiver logado (ex: tela de login), não mostra o menu -->
            <div class="container mt-4">
            <?php endif; ?>