<?php if (isset($mensagem) && $mensagem): ?>
    <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show">
        <?= htmlspecialchars($mensagem['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<h1>Devolução de Material - <?= $requisicao['numero'] ?></h1>
<div class="card shadow-sm mt-4 border-warning">
    <div class="card-body">
        <div class="alert alert-info py-2">
            <small>Informe abaixo a quantidade que está sendo devolvida ao estoque. O material retornará para o almoxarifado <strong><?= $requisicao['almoxarifado'] ?></strong>.</small>
        </div>

        <form action="?controller=requisicao&action=storeDevolucao" method="POST">
            <input type="hidden" name="id" value="<?= $requisicao['id'] ?>">

            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Produto</th>
                        <th>Qtd em Posse</th>
                        <th class="bg-warning text-dark">Qtd a Devolver</th>
                        <th>Lote (se aplicável)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requisicao['itens'] as $item): ?>
                        <?php if ($item['quantidade_atendida'] > 0): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['codigo'] . ' - ' . $item['descricao']) ?></td>
                            <td><?= number_format($item['quantidade_atendida'], 2, ',', '.') ?> <?= $item['unidade'] ?></td>
                            <td>
                                <input type="number" step="0.001" name="qtd_devolver[<?= $item['id'] ?>]" 
                                       class="form-control form-control-sm" max="<?= $item['quantidade_atendida'] ?>" min="0" value="0">
                            </td>
                            <td>
                                <?php if ($item['controla_lote']): ?>
                                    <select name="lote_id[<?= $item['id'] ?>]" class="form-select form-select-sm">
                                        <option value="">Selecione o Lote...</option>
                                        <?php foreach ($lotesPorItem[$item['id']] as $lote): ?>
                                            <option value="<?= $lote['id'] ?>">
                                                <?= htmlspecialchars($lote['numero_lote']) ?> (Val: <?= $lote['validade'] ? date('d/m/Y', strtotime($lote['validade'])) : 'N/A' ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <hr>
            <button type="submit" class="btn btn-warning">Confirmar Devolução</button>
            <a href="?controller=requisicao&action=show&id=<?= $requisicao['id'] ?>" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>