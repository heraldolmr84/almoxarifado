<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Almoxarifados</h1>
    <a href="?controller=almoxarifado&action=create" class="btn btn-primary">Novo Almoxarifado</a>
</div>
<?php if (isset($mensagem) && $mensagem): ?>
    <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show"><?= htmlspecialchars($mensagem['texto']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<div class="card shadow-sm">
    <table class="table table-striped mb-0">
        <thead class="table-dark"><tr><th>ID</th><th>Nome</th><th>Localização</th><th class="text-end">Ações</th></tr></thead>
        <tbody>
            <?php foreach ($almoxarifados as $a): ?>
            <tr>
                <td><?= $a['id'] ?></td>
                <td><?= htmlspecialchars($a['nome']) ?></td>
                <td><?= htmlspecialchars($a['localizacao'] ?? '—') ?></td>
                <td class="text-end">
                    <a href="?controller=almoxarifado&action=edit&id=<?= $a['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                    <a href="?controller=almoxarifado&action=delete&id=<?= $a['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Desativar?');">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>