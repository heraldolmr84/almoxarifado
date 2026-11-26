<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Almoxarifado</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            flex-direction: column;
            /* <-- ADICIONE ESTA LINHA */
            align-items: center;
            justify-content: center;
            gap: 20px;
            /* <-- Opcional: dá um espaço entre o H1 e o card */
        }

        .card-login {
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>

<body>

    <div style="text-align: center;">
        <img src="assets/img/logo.png" alt="Logo Facom" style="max-width: 100px; margin-bottom: 10px;">
        <h4 style="font-size: 1.2em">Fazer login no Sistema</h4>
    </div>

    <div class="card card-login shadow-sm">

        <div class="card-body p-4">


            <!-- MENSAGEM DE ERRO DE LOGIN -->
            <?php if (isset($_SESSION['erro_login'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['erro_login'] ?></div>
                <?php unset($_SESSION['erro_login']); ?>
            <?php endif; ?>

            <!-- MENSAGEM DE SUCESSO (Ex: Senha alterada) -->
            <?php if (isset($_SESSION['msg_sucesso_login'])): ?>
                <div class="alert alert-success"><?= $_SESSION['msg_sucesso_login'] ?></div>
                <?php unset($_SESSION['msg_sucesso_login']); ?>
            <?php endif; ?>

            <!-- MENSAGEM DE RECUPERAÇÃO DE SENHA ENVIADA -->
            <?php if (isset($_SESSION['msg_recuperacao'])): ?>
                <div class="alert alert-info"><?= $_SESSION['msg_recuperacao'] ?></div>
                <?php unset($_SESSION['msg_recuperacao']); ?>
            <?php endif; ?>

            <form action="?controller=auth&action=authenticate" method="POST">

                <?= \App\Core\Csrf::input() ?>
                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" required autofocus>
                </div>

                <div class="mb-4">
                    <label class="form-label">Senha</label>
                    <input type="password" name="senha" class="form-control" required>

                    <!-- LINK DE ESQUECI A SENHA (Adicionado aqui) -->
                    <div class="text-end mt-2">
                        <a href="?controller=auth&action=forgot" class="text-decoration-none small">Esqueci minha
                            senha</a>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>
        </div>
    </div>

    <script src="/assets/boostrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>