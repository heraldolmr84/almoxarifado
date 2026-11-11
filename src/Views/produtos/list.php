<?php if (isset($mensagem) && $mensagem): ?>
    <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($mensagem['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Produtos</h1>
    <a href="?controller=produto&action=create" class="btn btn-primary">Novo Produto</a>
</div>

<!-- Formulário de Busca -->
<div class="card shadow-sm mb-4">
    <div class="card-body py-3 bg-light">
        <form method="GET" action="" class="row gx-2 align-items-end">
            <input type="hidden" name="controller" value="produto">
            <input type="hidden" name="action" value="list">
            
            <div class="col-md-3">
                <label class="form-label mb-1 small text-muted">Código (SKU)</label>
                <input type="text" name="codigo" class="form-control form-control-sm" value="<?= htmlspecialchars($filtros['codigo']) ?>" placeholder="Ex: 26010004">
            </div>
            
            <div class="col-md-4">
                <label class="form-label mb-1 small text-muted">Descrição</label>
                <input type="text" name="descricao" class="form-control form-control-sm" value="<?= htmlspecialchars($filtros['descricao']) ?>" placeholder="Nome do produto...">
            </div>

            <div class="col-md-3">
                <label class="form-label mb-1 small text-muted">Categoria</label>
                <select name="categoria_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $filtros['categoria_id'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filtrar</button>
                <?php if (!empty($filtros['codigo']) || !empty($filtros['descricao']) || !empty($filtros['categoria_id'])): ?>
                    <a href="?controller=produto&action=list" class="btn btn-outline-secondary btn-sm w-100 mt-1">Limpar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Código</th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Unidade</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($produtos)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Nenhum produto cadastrado ainda.</td>
                    </tr>
                <?php endif; ?>
                
                <?php foreach ($produtos as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['codigo']) ?></td>
                    <td><?= htmlspecialchars($p['descricao']) ?></td>
                    <td><?= htmlspecialchars($p['categoria'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($p['unidade']) ?></td>
                    <td class="text-end">
                        <a href="?controller=produto&action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                        <a href="?controller=produto&action=delete&id=<?= $p['id'] ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Tem certeza que deseja desativar este produto?');">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- INÍCIO DA PAGINAÇÃO -->
        <?php if ($totalPaginas > 1): ?>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <small class="text-muted">Mostrando página <?= $page ?> de <?= $totalPaginas ?> (Total: <?= $totalRegistros ?? '' ?> registros)</small>
                
                <nav aria-label="Navegação de páginas">
                    <ul class="pagination pagination-sm mb-0">
                        
                        <!-- Botão Anterior -->
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Anterior</a>
                        </li>

                        <!-- Números das Páginas -->
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Botão Próximo -->
                        <li class="page-item <?= $page >= $totalPaginas ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Próximo</a>
                        </li>
                        
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
        <!-- FIM DA PAGINAÇÃO -->
    </div>
</div>