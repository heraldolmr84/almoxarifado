<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Detalhes da Movimentação #<?= $mov['id'] ?></h2>
    <a href="?controller=movimentacao&action=list" class="btn btn-secondary">Voltar</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-3">
                <strong>Data:</strong><br>
                <?= date('d/m/Y H:i', strtotime($mov['data_movimentacao'])) ?>
            </div>
            <div class="col-md-3 mb-3">
                <strong>Tipo:</strong><br>
                <span class="badge <?= $mov['tipo'] == 'ENTRADA' ? 'bg-success' : 'bg-danger' ?> fs-6">
                    <?= $mov['tipo'] ?> <?= $mov['subtipo'] ? '(' . $mov['subtipo'] . ')' : '' ?>
                </span>
            </div>
            <div class="col-md-3 mb-3">
                <strong>Responsável:</strong><br>
                <?= htmlspecialchars($mov['usuario']) ?>
            </div>
            <div class="col-md-3 mb-3">
                <strong>Documento:</strong><br>
                <?= htmlspecialchars($mov['documento'] ?? 'Não informado') ?>
            </div>
        </div>

        <hr>

        <div class="row">
            <?php if ($mov['tipo'] == 'ENTRADA'): ?>
                <div class="col-md-6 mb-3">
                    <strong>Almoxarifado de Destino:</strong><br>
                    <?= htmlspecialchars($mov['almox_destino']) ?>
                </div>
            <?php else: ?>
                <div class="col-md-6 mb-3">
                    <strong>Almoxarifado de Origem:</strong><br>
                    <?= htmlspecialchars($mov['almox_origem']) ?>
                </div>
            <?php endif; ?>

            <div class="col-md-6 mb-3">
                <strong>Departamento Solicitante:</strong><br>
                <?= htmlspecialchars($mov['departamento'] ?? '—') ?>
            </div>
        </div>

        <?php if (!empty($mov['numero_requisicao'])): ?>
            <div class="alert alert-info py-2 mb-0">
                Esta movimentação foi gerada pelo atendimento da
                <a href="?controller=requisicao&action=show&id=<?= $mov['requisicao_id'] ?>" class="alert-link">
                    Requisição <?= $mov['numero_requisicao'] ?>
                </a>.
            </div>
        <?php endif; ?>

        <?php if (!empty($mov['observacao'])): ?>
            <div class="mt-3">
                <strong>Observações:</strong><br>
                <span class="text-muted"><?= nl2br(htmlspecialchars($mov['observacao'])) ?></span>
            </div>
        <?php endif; ?>
    </div>
</div>

<h5 class="mb-3">Itens Movimentados</h5>
<div class="card shadow-sm">
    <table class="table table-striped mb-0">
        <thead class="table-dark">
            <tr>
                <th>Código</th>
                <th>Produto</th>
                <th>Lote / Validade</th> <!-- NOVA COLUNA -->
                <th class="text-end">Quantidade</th>
                <th class="text-end">Valor Unitário</th>
                <th class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalGeral = 0;
            foreach ($mov['itens'] as $item):
                $subtotal = ($item['valor_unitario'] ?? 0) * $item['quantidade'];
                $totalGeral += $subtotal;
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['codigo']) ?></td>
                    <td><?= htmlspecialchars($item['descricao']) ?></td>

                    <!-- NOVA COLUNA DE EXIBIÇÃO DO LOTE -->
                    <td>
                        <?php if (!empty($item['numero_lote'])): ?>
                            <strong><?= htmlspecialchars($item['numero_lote']) ?></strong><br>
                            <small class="text-muted">Val:
                                <?= $item['validade'] ? date('d/m/Y', strtotime($item['validade'])) : 'N/A' ?></small>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>

                    <td class="text-end"><?= number_format($item['quantidade'], 3, ',', '.') ?>     <?= $item['unidade'] ?></td>
                    <td class="text-end">
                        <?= $item['valor_unitario'] ? 'R$ ' . number_format($item['valor_unitario'], 2, ',', '.') : '—' ?>
                    </td>
                    <td class="text-end">
                        <?= $item['valor_unitario'] ? 'R$ ' . number_format($subtotal, 2, ',', '.') : '—' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <?php if ($totalGeral > 0): ?>
            <tfoot class="table-light">
                <tr>
                    <th colspan="4" class="text-end">Total Geral:</th>
                    <th class="text-end">R$ <?= number_format($totalGeral, 2, ',', '.') ?></th>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>
</div>