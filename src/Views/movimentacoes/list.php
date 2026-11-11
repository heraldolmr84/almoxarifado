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

<!-- Formulário de Busca -->
<div class="card shadow-sm mb-4">
    <div class="card-body py-3 bg-light">
        <form method="GET" action="" class="row gx-2 align-items-end">
            <input type="hidden" name="controller" value="movimentacao">
            <input type="hidden" name="action" value="list">
            
            <div class="col-md-2">
                <label class="form-label mb-1 small text-muted">Tipo</label>
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="ENTRADA" <?= $filtros['tipo'] == 'ENTRADA' ? 'selected' : '' ?>>Entrada</option>
                    <option value="SAIDA" <?= $filtros['tipo'] == 'SAIDA' ? 'selected' : '' ?>>Saída</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label mb-1 small text-muted">Documento (NF/Recibo)</label>
                <input type="text" name="documento" class="form-control form-control-sm" value="<?= htmlspecialchars($filtros['documento']) ?>" placeholder="Nº do documento">
            </div>

            <div class="col-md-2">
                <label class="form-label mb-1 small text-muted">Data Inicial</label>
                <input type="date" name="data_inicio" class="form-control form-control-sm" value="<?= htmlspecialchars($filtros['data_inicio']) ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label mb-1 small text-muted">Data Final</label>
                <input type="date" name="data_fim" class="form-control form-control-sm" value="<?= htmlspecialchars($filtros['data_fim']) ?>">
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filtrar</button>
                <?php if (!empty($filtros['tipo']) || !empty($filtros['documento']) || !empty($filtros['data_inicio']) || !empty($filtros['data_fim'])): ?>
                    <a href="?controller=movimentacao&action=list" class="btn btn-outline-secondary btn-sm w-100 mt-1">Limpar Filtros</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
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