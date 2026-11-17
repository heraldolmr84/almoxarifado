<style>
    @media print {
        @page {
            size: portrait;
            margin: 1.5cm;
        }
    }
</style>

<!-- Cabeçalho para Impressão -->
<div class="relatorio-header">
    <h4 style="margin: 0; font-weight: bold;">SISGEM - Sistema de Gestão de Materiais</h4>
    <p style="margin: 5px 0; font-size: 14px;">Relatório de Consumo por Setor</p>
    <p style="margin: 0; font-size: 11px; color: #666;">
        Período: <?= date('d/m/Y', strtotime($filtros['data_inicio'])) ?> a
        <?= date('d/m/Y', strtotime($filtros['data_fim'])) ?> |
        Emitido em: <?= date('d/m/Y H:i') ?>
    </p>
</div>

<!-- Título e Botões (Não imprimem) -->
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h1>Consumo por Setor</h1>
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
            <input type="hidden" name="action" value="consumo">
            <div class="col-md-4">
                <label class="form-label mb-1 small">Data Inicial</label>
                <input type="date" name="data_inicio" class="form-control form-control-sm"
                    value="<?= $filtros['data_inicio'] ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label mb-1 small">Data Final</label>
                <input type="date" name="data_fim" class="form-control form-control-sm"
                    value="<?= $filtros['data_fim'] ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filtrar Relatório</button>
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
                    <th>Departamento / Setor</th>
                    <th class="text-center">Qtd. de Requisições</th>
                    <th class="text-end">Total de Itens Consumidos</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dados)): ?>
                    <tr>
                        <td colspan="3" class="text-center py-4 text-muted">Nenhum consumo registrado neste período.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($dados as $d): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($d['departamento']) ?></strong></td>
                        <td class="text-center"><?= $d['qtd_requisicoes'] ?></td>
                        <td class="text-end fw-bold"><?= number_format($d['total_itens'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>