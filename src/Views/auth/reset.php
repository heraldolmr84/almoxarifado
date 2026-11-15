<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Nova Senha - Almoxarifado</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>body { background-color: #f4f6f9; height: 100vh; display: flex; align-items: center; justify-content: center; } .card { width: 100%; max-width: 400px; }</style>
</head>
<body>
<div class="card shadow-sm">
    <div class="card-body p-4">
        <h4 class="text-center mb-4">Criar Nova Senha</h4>
        
        <?php if (isset($_SESSION['erro_reset'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['erro_reset'] ?></div>
            <?php unset($_SESSION['erro_reset']); ?>
        <?php endif; ?>

        <form action="?controller=auth&action=updatePassword" method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
            
            <div class="mb-3">
                <label class="form-label">Nova Senha</label>
                <input type="password" name="senha" class="form-control" required minlength="6">
            </div>
            <div class="mb-4">
                <label class="form-label">Confirme a Nova Senha</label>
                <input type="password" name="confirma_senha" class="form-control" required minlength="6">
            </div>
            <button type="submit" class="btn btn-success w-100">Salvar Nova Senha</button>
        </form>
    </div>
</div>
</body>
</html>