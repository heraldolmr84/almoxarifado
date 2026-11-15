<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Troca de Senha Obrigatória</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>body { background-color: #f4f6f9; height: 100vh; display: flex; align-items: center; justify-content: center; } .card { width: 100%; max-width: 400px; }</style>
</head>
<body>
<div class="card shadow-sm">
    <div class="card-body p-4">
        <h4 class="text-center mb-3">Nova Senha</h4>
        
        <?php if (isset($_SESSION['msg_alerta'])): ?>
            <div class="alert alert-warning"><?= $_SESSION['msg_alerta'] ?></div>
            <?php unset($_SESSION['msg_alerta']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['msg_erro'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['msg_erro'] ?></div>
            <?php unset($_SESSION['msg_erro']); ?>
        <?php endif; ?>

        <p class="text-muted small text-center mb-4">Para sua segurança, crie uma nova senha antes de continuar.</p>

        <form action="?controller=auth&action=salvarTrocaSenha" method="POST">
            <div class="mb-3">
                <label class="form-label">Nova Senha</label>
                <input type="password" name="senha" class="form-control" required minlength="6" autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">Confirme a Nova Senha</label>
                <input type="password" name="confirma_senha" class="form-control" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary w-100">Salvar Nova Senha</button>
        </form>
    </div>
</div>
</body>
</html>