<?php if (isset($mensagem) && $mensagem): ?>
    <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($mensagem['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Produtos</h1>
    <a href="?controller=produto&action=create" class="btn btn-primary">Novo Produto</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Código</th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Unidade</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($produtos)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Nenhum produto cadastrado ainda.</td>
                    </tr>
                <?php endif; ?>
                
                <?php foreach ($produtos as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['codigo']) ?></td>
                    <td><?= htmlspecialchars($p['descricao']) ?></td>
                    <td><?= htmlspecialchars($p['categoria'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($p['unidade']) ?></td>
                    <td class="text-end">
                        <a href="?controller=produto&action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                        <a href="?controller=produto&action=delete&id=<?= $p['id'] ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Tem certeza que deseja desativar este produto?');">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>