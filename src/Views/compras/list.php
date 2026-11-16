<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Pedidos de Compra</h1>
    <a href="?controller=pedidoCompra&action=create" class="btn btn-primary">Novo Pedido</a>
</div>

<div class="card shadow-sm">
    <table class="table table-striped mb-0">
        <thead class="table-dark">
            <tr><th>Número</th><th>Data</th><th>Solicitante</th><th>Status</th><th>Ações</th></tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $p): ?>
            <tr>
                <td><a href="?controller=pedidoCompra&action=show&id=<?= $p['id'] ?>"><strong><?= $p['numero'] ?></strong></a></td>
                <td><?= date('d/m/Y', strtotime($p['data_criacao'])) ?></td>
                <td><?= htmlspecialchars($p['solicitante']) ?></td>
                <td><span class="badge bg-secondary"><?= $p['status'] ?></span></td>
                <td><a href="?controller=pedidoCompra&action=show&id=<?= $p['id'] ?>" class="btn btn-sm btn-info text-white">Ver</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>