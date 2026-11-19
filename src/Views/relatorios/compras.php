<!-- Cabeçalho para Impressão -->
<div class="relatorio-header">
    <h4 style="margin: 0; font-weight: bold;">SISGEM - Sistema de Gestão de Materiais</h4>
    <p style="margin: 5px 0; font-size: 14px;">Relatório de Compras por Período</p>
    <p style="margin: 0; font-size: 11px; color: #666;">
        Período: <?= date('d/m/Y', strtotime($filtros['data_inicio'])) ?> a <?= date('d/m/Y', strtotime($filtros['data_fim'])) ?> | 
        Emitido em: <?= date('d/m/Y H:i') ?>
    </p>
</div>

<!-- Título e Botões (Não imprimem) -->
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h1>Compras por Período</h1>
    <div>
        <button onclick="window.print()" class="btn btn-outline-dark me-2">🖨️ Imprimir / PDF</button>
        <a href="?controller=relatorio&action=index" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<!-- Filtros -->
<div class="card shadow-sm mb-4 no-print">
    <div class="card-body py-3 bg-light">
        <form method="GET" action="" class="row gx-2 align-items-end">
            <input type="hidden" name="controller" value="relatorio">
            <input type="hidden" name="action" value="compras">
            <div class="col-md-4">
                <label class="form-label mb-1 small">Data Inicial</label>
                <input type="date" name="data_inicio" class="form-control form-control-sm" value="<?= $filtros['data_inicio'] ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label mb-1 small">Data Final</label>
                <input type="date" name="data_fim" class="form-control form-control-sm" value="<?= $filtros['data_fim'] ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Tabela -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="relatorio-tabela mb-0">
            <thead>
                <tr>
                    <th>Nº Pedido</th>
                    <th>Data da Compra</th>
                    <th>Fornecedor</th>
                    <th>Status</th>
                    <th class="text-end">Valor Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalGeralPeriodo = 0;
                if (empty($dados)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Nenhuma compra registrada neste período.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($dados as $d): 
                    $totalGeralPeriodo += $d['total_pedido'];
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($d['documento']) ?></strong></td>
                    <td><?= $d['data_compra'] ? date('d/m/Y', strtotime($d['data_compra'])) : '—' ?></td>
                    <td><?= htmlspecialchars($d['fornecedor'] ?? 'Não informado') ?></td>
                    <td><?= htmlspecialchars($d['subtipo']) ?></td>
                    <td class="text-end">R$ <?= number_format($d['total_pedido'], 2, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end fs-6">TOTAL GERAL DO PERÍODO:</th>
                    <th class="text-end fs-6" style="color: #198754;">R$ <?= number_format($totalGeralPeriodo, 2, ',', '.') ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>