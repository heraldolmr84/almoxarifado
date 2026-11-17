<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Posição de Estoque</h1>
</div>

<!-- Formulário de Busca -->
<div class="card shadow-sm mb-4">
    <div class="card-body py-3 bg-light">
        <form method="GET" action="" class="row gx-2 align-items-end">
            <input type="hidden" name="controller" value="estoque">
            <input type="hidden" name="action" value="list">
            
            <div class="col-md-3">
                <label class="form-label mb-1 small text-muted">Código (SKU)</label>
                <input type="text" name="codigo" class="form-control form-control-sm" value="<?= htmlspecialchars($filtros['codigo']) ?>">
            </div>
            
            <div class="col-md-4">
                <label class="form-label mb-1 small text-muted">Descrição</label>
                <input type="text" name="descricao" class="form-control form-control-sm" value="<?= htmlspecialchars($filtros['descricao']) ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label mb-1 small text-muted">Almoxarifado</label>
                <select name="almoxarifado_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($almoxarifados as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= $filtros['almoxarifado_id'] == $a['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['nome']) ?>
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

<div class="card shadow-sm">
    <table class="table table-striped table-hover mb-0">
        <thead class="table-dark">
            <tr>
                <th>Código</th>
                <th>Produto</th>
                <th>Almoxarifado</th>
                <th>Lote / Validade</th>
                <th class="text-end">Qtd em Estoque</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($estoque)): ?>
                <tr><td colspan="5" class="text-center py-4 text-muted">Nenhum produto em estoque com os filtros atuais.</td></tr>
            <?php endif; ?>

            <?php foreach ($estoque as $item): 
                // Lógica para destacar se estiver abaixo do mínimo
                $abaixoMinimo = ($item['quantidade'] <= $item['estoque_minimo']);
            ?>
            <tr>
                <td><?= htmlspecialchars($item['codigo']) ?></td>
                <td><?= htmlspecialchars($item['descricao']) ?></td>
                <td><?= htmlspecialchars($item['almoxarifado']) ?></td>
                <td>
                    <?php if ($item['numero_lote']): ?>
                        <strong><?= htmlspecialchars($item['numero_lote']) ?></strong><br>
                        <small class="text-muted">Val: <?= $item['validade'] ? date('d/m/Y', strtotime($item['validade'])) : 'N/A' ?></small>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td class="text-end fw-bold <?= $abaixoMinimo ? 'text-danger' : 'text-success' ?> fs-5">
                    <?= number_format($item['quantidade'], 2, ',', '.') ?> <?= $item['unidade'] ?>
                    <?php if ($abaixoMinimo): ?>
                        <br><small class="badge bg-danger">Abaixo do Mínimo (<?= number_format($item['estoque_minimo'], 2, ',', '.') ?>)</small>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Paginação -->
    <?php if ($totalPaginas > 1): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">Mostrando página <?= $page ?> de <?= $totalPaginas ?> (Total: <?= $totalRegistros ?> registros)</small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Anterior</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPaginas ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Próximo</a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>