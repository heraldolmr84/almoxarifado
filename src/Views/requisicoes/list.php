<?php if (isset($mensagem) && $mensagem): ?>
    <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show">
        <?= htmlspecialchars($mensagem['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Requisições</h1>
    <a href="?controller=requisicao&action=create" class="btn btn-primary">Nova Requisição</a>
</div>

<!-- Formulário de Busca -->
<div class="card shadow-sm mb-4">
    <div class="card-body py-3 bg-light">
        <form method="GET" action="" class="row gx-2 align-items-end">
            <input type="hidden" name="controller" value="requisicao">
            <input type="hidden" name="action" value="list">
            
            <div class="col-md-3">
                <label class="form-label mb-1 small text-muted">Nº Requisição</label>
                <input type="text" name="numero" class="form-control form-control-sm" value="<?= htmlspecialchars($filtros['numero']) ?>" placeholder="Ex: REQ-0001">
            </div>
            
            <div class="col-md-3">
                <label class="form-label mb-1 small text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="RASCUNHO" <?= $filtros['status'] == 'RASCUNHO' ? 'selected' : '' ?>>Rascunho</option>
                    <option value="AGUARDANDO_APROVACAO" <?= $filtros['status'] == 'AGUARDANDO_APROVACAO' ? 'selected' : '' ?>>Aguardando Aprovação</option>
                    <option value="APROVADA" <?= $filtros['status'] == 'APROVADA' ? 'selected' : '' ?>>Aprovada</option>
                    <option value="ATENDIDA_PARCIAL" <?= $filtros['status'] == 'ATENDIDA_PARCIAL' ? 'selected' : '' ?>>Parcial</option>
                    <option value="ATENDIDA" <?= $filtros['status'] == 'ATENDIDA' ? 'selected' : '' ?>>Atendida</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label mb-1 small text-muted">Departamento</label>
                <select name="departamento_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($departamentos as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= $filtros['departamento_id'] == $d['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filtrar</button>
                <?php if (!empty($filtros['numero']) || !empty($filtros['status']) || !empty($filtros['departamento_id'])): ?>
                    <a href="?controller=requisicao&action=list" class="btn btn-outline-secondary btn-sm w-100 mt-1">Limpar Filtros</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <table class="table table-striped mb-0">
        <thead class="table-dark">
            <tr>
                <th>Número</th>
                <th>Data</th>
                <th>Departamento</th>
                <th>Solicitante</th>
                <th>Prioridade</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($requisicoes)): ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">Nenhuma requisição encontrada.</td></tr>
            <?php endif; ?>
            
            <?php foreach ($requisicoes as $r): 
                $cores = [
                    'RASCUNHO' => 'bg-secondary',
                    'AGUARDANDO_APROVACAO' => 'bg-warning text-dark',
                    'APROVADA' => 'bg-info text-dark',
                    'REJEITADA' => 'bg-danger',
                    'ATENDIDA' => 'bg-success'
                ];
                $cor = $cores[$r['status']] ?? 'bg-secondary';
            ?>
            <tr>
                <td><a href="?controller=requisicao&action=show&id=<?= $r['id'] ?>"><strong><?= $r['numero'] ?></strong></a></td>
                <td><?= date('d/m/Y H:i', strtotime($r['criado_em'])) ?></td>
                <td><?= htmlspecialchars($r['departamento']) ?></td>
                <td><?= htmlspecialchars($r['solicitante']) ?></td>
                <td><?= $r['prioridade'] ?></td>
                <td><span class="badge <?= $cor ?>"><?= $r['status'] ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- PAGINAÇÃO -->
    <?php if ($totalPaginas > 1): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">Mostrando página <?= $page ?> de <?= $totalPaginas ?> (Total: <?= $totalRegistros ?> registros)</small>
            <nav aria-label="Navegação de páginas">
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