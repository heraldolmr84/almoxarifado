<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Categorias</h1>
    <a href="?controller=categoria&action=create" class="btn btn-primary">Nova Categoria</a>
</div>

<div class="card shadow-sm">
    <table class="table table-striped mb-0">
        <thead class="table-dark">
            <tr>
                <th>ID</th>                
                <th>Nome</th>
                <th>Prefixo SKU</th>
                <th>Categoria Pai</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categorias as $c): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td><?= htmlspecialchars($c['nome']) ?></td>
                    <td><?= htmlspecialchars($c['prefixo_sku'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($c['pai_nome'] ?? '—') ?></td>
                    <td class="text-end">
                        <a href="?controller=categoria&action=edit&id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                        <a href="?controller=categoria&action=delete&id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Desativar?');">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>