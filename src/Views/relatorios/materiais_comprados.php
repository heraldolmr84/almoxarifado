<!-- Cabeçalho para Impressão -->
<div class="relatorio-header">
    <h4 style="margin: 0; font-weight: bold;">SISGEM - Sistema de Gestão de Materiais</h4>
    <p style="margin: 5px 0; font-size: 14px;">Relatório de Materiais Comprados</p>
    <p style="margin: 0; font-size: 11px; color: #666;">
        Período: <?= date('d/m/Y', strtotime($filtros['data_inicio'])) ?> a <?= date('d/m/Y', strtotime($filtros['data_fim'])) ?> | 
        Emitido em: <?= date('d/m/Y H:i') ?>
    </p>
</div>

<!-- Título e Botões -->
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h1>Materiais Comprados</h1>
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
            <input type="hidden" name="action" value="materiaisComprados">
            
            <div class="col-md-3">
                <label class="form-label mb-1 small">Data Inicial</label>
                <input type="date" name="data_inicio" class="form-control form-control-sm" value="<?= $filtros['data_inicio'] ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1 small">Data Final</label>
                <input type="date" name="data_fim" class="form-control form-control-sm" value="<?= $filtros['data_fim'] ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label mb-1 small">Material (Opcional)</label>
                <select name="produto_id" class="form-select form-select-sm select2">
                    <option value="">Todos os materiais</option>
                    <?php foreach ($produtos as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $filtros['produto_id'] == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['codigo'] . ' - ' . $p['descricao']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
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
                    <th>Data</th>
                    <th>Nº Pedido</th>
                    <th>Fornecedor</th>
                    <th>Produto</th>
                    <th class="text-end">Qtd</th>
                    <th class="text-end">Vlr. Unit.</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalGeral = 0;
                if (empty($dados)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Nenhum material comprado encontrado com estes filtros.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($dados as $d): 
                    $totalGeral += $d['subtotal'];
                ?>
                <tr>
                    <td><?= $d['data_compra'] ? date('d/m/Y', strtotime($d['data_compra'])) : '—' ?></td>
                    <td><?= htmlspecialchars($d['pedido_numero']) ?></td>
                    <td><?= htmlspecialchars($d['fornecedor'] ?? '—') ?></td>
                    <td>
                        <strong><?= htmlspecialchars($d['produto_codigo']) ?></strong> - <?= htmlspecialchars($d['produto_descricao']) ?>
                    </td>
                    <td class="text-end"><?= number_format($d['quantidade'], 2, ',', '.') ?> <?= $d['unidade'] ?></td>
                    <td class="text-end">R$ <?= number_format($d['valor_unitario'] ?? 0, 2, ',', '.') ?></td>
                    <td class="text-end">R$ <?= number_format($d['subtotal'], 2, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="6" class="text-end fs-6">TOTAL GERAL:</th>
                    <th class="text-end fs-6" style="color: #198754;">R$ <?= number_format($totalGeral, 2, ',', '.') ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>