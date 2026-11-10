<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Almoxarifado</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card-login { width: 100%; max-width: 400px; }
    </style>
</head>
<body>

<div class="card card-login shadow-sm">
    <div class="card-body p-4">
        <h3 class="text-center mb-4">Login no sistema</h3>
        
        <?php if (isset($_SESSION['erro_login'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['erro_login'] ?></div>
            <?php unset($_SESSION['erro_login']); ?>
        <?php endif; ?>

        <form action="?controller=auth&action=authenticate" method="POST">
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">Senha</label>
                <input type="password" name="senha" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>
</div>

<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>