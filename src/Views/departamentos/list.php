<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Departamentos</h1>
    <a href="?controller=departamento&action=create" class="btn btn-primary">Novo Departamento</a>
</div>

<?php if (isset($mensagem) && $mensagem): ?>
    <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show">
        <?= htmlspecialchars($mensagem['texto']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <table class="table table-striped mb-0">
        <thead class="table-dark">
            <tr><th>ID</th><th>Nome</th><th>Qtd. Almoxarifados</th><th class="text-end">Ações</th></tr>
        </thead>
        <tbody>
            <?php foreach ($departamentos as $d): ?>
            <tr>
                <td><?= $d['id'] ?></td>
                <td><?= htmlspecialchars($d['nome']) ?></td>
                <td><span class="badge bg-info text-dark"><?= $d['total_almox'] ?> vinculados</span></td>
                <td class="text-end">
                    <a href="?controller=departamento&action=edit&id=<?= $d['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                    <a href="?controller=departamento&action=delete&id=<?= $d['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Desativar?');">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>