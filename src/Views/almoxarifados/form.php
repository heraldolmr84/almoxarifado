<?php $isEdit = isset($almoxarifado); $action = $isEdit ? '?controller=almoxarifado&action=update' : '?controller=almoxarifado&action=store'; ?>
<h1><?= $isEdit ? 'Editar Almoxarifado' : 'Novo Almoxarifado' ?></h1>
<div class="card shadow-sm mt-4"><div class="card-body">
    <form action="<?= $action ?>" method="POST">
        <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $almoxarifado['id'] ?>"><?php endif; ?>
        <div class="mb-3">
            <label class="form-label">Nome do Almoxarifado *</label>
            <input type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($almoxarifado['nome'] ?? '') ?>">
        </div>
        <div class="mb-4">
            <label class="form-label">Localização / Sala</label>
            <input type="text" name="localizacao" class="form-control" value="<?= htmlspecialchars($almoxarifado['localizacao'] ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="?controller=almoxarifado&action=list" class="btn btn-secondary">Cancelar</a>
    </form>
</div></div>