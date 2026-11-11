<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Fila de Espera (Backorders)</h1>
    <a href="?controller=requisicao&action=list" class="btn btn-secondary">Voltar</a>
</div>

<div class="card shadow-sm border-warning">
    <div class="card-header bg-warning text-dark fw-bold">
        Itens Pendentes de Entrega
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Requisição</th>
                    <th>Data</th>
                    <th>Produto</th>
                    <th>Depto</th>
                    <th class="text-end">Falta Entregar</th>
                    <th class="text-end">Estoque Atual</th>
                    <th class="text-center">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($backorders)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-success">
                            <strong>Não há itens pendentes! Todo o estoque aprovado já foi entregue.</strong>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($backorders as $b): ?>
                <tr>
                    <td><strong><?= $b['numero'] ?></strong></td>
                    <td><?= date('d/m/Y', strtotime($b['criado_em'])) ?></td>
                    <td><?= htmlspecialchars($b['codigo'] . ' - ' . $b['descricao']) ?></td>
                    <td><?= htmlspecialchars($b['departamento']) ?></td>
                    <td class="text-end text-danger fw-bold"><?= number_format($b['quantidade_pendente'], 3, ',', '.') ?></td>
                    
                    <td class="text-end fw-bold <?= $b['estoque_atual'] >= $b['quantidade_pendente'] ? 'text-success' : 'text-muted' ?>">
                        <?= number_format($b['estoque_atual'], 3, ',', '.') ?>
                    </td>
                    
                    <td class="text-center">
                        <?php if ($b['estoque_atual'] >= $b['quantidade_pendente']): ?>
                            <a href="?controller=requisicao&action=show&id=<?= $b['requisicao_id'] ?>" class="btn btn-sm btn-success">
                                Atender Agora
                            </a>
                        <?php else: ?>
                            <span class="badge bg-secondary">Aguardando Compra</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>