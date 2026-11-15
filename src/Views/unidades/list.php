<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Unidades de Medida</h1>
    <a href="?controller=unidade&action=create" class="btn btn-primary">Nova Unidade</a>
</div>

<?php if (isset($mensagem) && $mensagem): ?>
    <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show">
        <?= htmlspecialchars($mensagem['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <table class="table table-striped mb-0">
        <thead class="table-dark">
            <tr><th>ID</th><th>Sigla</th><th>Descrição</th><th class="text-end">Ações</th></tr>
        </thead>
        <tbody>
            <?php foreach ($unidades as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($u['sigla']) ?></span></td>
                <td><?= htmlspecialchars($u['descricao']) ?></td>
                <td class="text-end">
                    <a href="?controller=unidade&action=edit&id=<?= $u['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                    <a href="?controller=unidade&action=delete&id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Excluir?');">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>