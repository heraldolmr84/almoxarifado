<?php if (isset($mensagem) && $mensagem): ?>
    <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show">
        <?= htmlspecialchars($mensagem['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Movimentações de Estoque</h1>
    <a href="?controller=movimentacao&action=entrada" class="btn btn-success">Nova Entrada</a>
    <a href="?controller=movimentacao&action=saida" class="btn btn-danger">Nova Saída</a>
</div>

<div class="card shadow-sm">
    <table class="table table-striped mb-0">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Data</th>
                <th>Tipo</th>
                <th>Documento</th>
                <th>Almoxarifado</th>
                <th>Usuário</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($movimentacoes)): ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">Nenhuma movimentação registrada.</td></tr>
            <?php endif; ?>
            
            <?php foreach ($movimentacoes as $m): ?>
            <tr>
                <td><a href="?controller=movimentacao&action=show&id=<?= $m['id'] ?>">#<?= $m['id'] ?></a></td>
                <td><?= date('d/m/Y H:i', strtotime($m['data_movimentacao'])) ?></td>
                <td>
                    <span class="badge <?= $m['tipo'] == 'ENTRADA' ? 'bg-success' : 'bg-danger' ?>">
                        <?= $m['tipo'] ?>
                    </span>
                    <?php if (!empty($m['subtipo'])): ?>
                        <br><small class="text-muted"><?= htmlspecialchars($m['subtipo']) ?></small>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($m['documento'] ?? '—') ?></td>
                <td><?= htmlspecialchars($m['almoxarifado'] ?? '—') ?></td>
                <td><?= htmlspecialchars($m['usuario']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>