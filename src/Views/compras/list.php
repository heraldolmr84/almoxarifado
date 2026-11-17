<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Pedidos de Compra</h1>
    <a href="?controller=pedidoCompra&action=create" class="btn btn-primary">Novo Pedido</a>
</div>

<div class="card shadow-sm">
    <table class="table table-striped mb-0">
        <thead class="table-dark">
            <tr>
                <th>Número</th>
                <th>Data</th>
                <th>Solicitante</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pedidos)): ?>
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">Nenhum pedido de compra encontrado.</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($pedidos as $p):
                // Array que mapeia o status para a cor do Bootstrap
                $cores = [
                    'RASCUNHO' => 'bg-secondary',
                    'AGUARDANDO_APROVACAO' => 'bg-warning text-dark',
                    'APROVADO' => 'bg-info text-dark',
                    'REJEITADO' => 'bg-danger',
                    'COMPRADO' => 'bg-primary',
                    'RECEBIDO' => 'bg-success',
                    'RECEBIDO_PARCIAL' => 'bg-warning text-dark' // Amarelo indicando que falta chegar algo
                ];
                // Se o status não estiver no array, usa cinza como padrão
                $corBadge = $cores[$p['status']] ?? 'bg-secondary';
                ?>
                <tr>
                    <td><a href="?controller=pedidoCompra&action=show&id=<?= $p['id'] ?>"><strong>
                                <?= $p['numero'] ?>
                            </strong></a></td>
                    <td>
                        <?= date('d/m/Y', strtotime($p['data_criacao'])) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($p['solicitante']) ?>
                    </td>
                    <td>
                        <span class="badge <?= $corBadge ?>">
                            <?= $p['status'] ?>
                        </span>
                    </td>
                    <td><a href="?controller=pedidoCompra&action=show&id=<?= $p['id'] ?>"
                            class="btn btn-sm btn-info text-white">Ver</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>