<?php 
$isEdit = isset($categoria); 
$action = $isEdit ? '?controller=categoria&action=update' : '?controller=categoria&action=store';
?>
<h1><?= $isEdit ? 'Editar Categoria' : 'Nova Categoria' ?></h1>
<div class="card shadow-sm mt-4">
    <div class="card-body">
        <form action="<?= $action ?>" method="POST">
            <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $categoria['id'] ?>"><?php endif; ?>
            
            <div class="mb-3">
                <label class="form-label">Nome da Categoria *</label>
                <input type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($categoria['nome'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Categoria Pai (Opcional)</label>
                <select name="categoria_pai_id" class="form-select">
                    <option value="">Nenhuma (Categoria Principal)</option>
                    <?php foreach ($pais as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= (isset($categoria['categoria_pai_id']) && $categoria['categoria_pai_id'] == $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="?controller=categoria&action=list" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>