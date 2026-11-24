<?php 
$isEdit = isset($unidade); 
$action = $isEdit ? '?controller=unidade&action=update' : '?controller=unidade&action=store';
?>
<h1><?= $isEdit ? 'Editar Unidade' : 'Nova Unidade' ?></h1>
<div class="card shadow-sm mt-4">
    <div class="card-body">
        <form action="<?= $action ?>" method="POST">
            <?= \App\Core\Csrf::input() ?>
            <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $unidade['id'] ?>"><?php endif; ?>
            
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sigla *</label>
                    <input type="text" name="sigla" class="form-control" required maxlength="10" value="<?= htmlspecialchars($unidade['sigla'] ?? '') ?>" placeholder="Ex: UN, KG, L">
                </div>
                <div class="col-md-9 mb-3">
                    <label class="form-label">Descrição *</label>
                    <input type="text" name="descricao" class="form-control" required maxlength="50" value="<?= htmlspecialchars($unidade['descricao'] ?? '') ?>" placeholder="Ex: Unidade, Quilograma">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="?controller=unidade&action=list" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>