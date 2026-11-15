<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Usuários</h1>
    <a href="?controller=usuario&action=create" class="btn btn-primary">Novo Usuário</a>
</div>

<?php if (isset($mensagem) && $mensagem): ?>
    <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show">
        <?= htmlspecialchars($mensagem['texto']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <table class="table table-striped mb-0">
        <thead class="table-dark">
            <tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th class="text-end">Ações</th></tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['nome']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><span class="badge bg-secondary"><?= $u['perfil'] ?></span></td>
                <td class="text-end">
                    <a href="?controller=usuario&action=edit&id=<?= $u['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                    <a href="?controller=usuario&action=delete&id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Desativar usuário?');">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>