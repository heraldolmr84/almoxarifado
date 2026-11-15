<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha - Almoxarifado</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>body { background-color: #f4f6f9; height: 100vh; display: flex; align-items: center; justify-content: center; } .card { width: 100%; max-width: 400px; }</style>
</head>
<body>
<div class="card shadow-sm">
    <div class="card-body p-4">
        <h4 class="text-center mb-3">Recuperar Senha</h4>
        <p class="text-muted small text-center mb-4">Informe seu e-mail cadastrado para receber o link de redefinição.</p>
        
        <form action="?controller=auth&action=sendForgot" method="POST">
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary w-100">Enviar Link</button>
            <div class="text-center mt-3">
                <a href="?controller=auth&action=login" class="text-decoration-none small">Voltar para o Login</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>