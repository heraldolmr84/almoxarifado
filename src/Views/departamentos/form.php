<?php 
$isEdit = isset($departamento); 
$action = $isEdit ? '?controller=departamento&action=update' : '?controller=departamento&action=store';
$almoxSelecionados = $departamento['almoxarifados'] ?? [];
?>
<h1><?= $isEdit ? 'Editar Departamento' : 'Novo Departamento' ?></h1>
<div class="card shadow-sm mt-4">
    <div class="card-body">
        <form action="<?= $action ?>" method="POST">
            <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $departamento['id'] ?>"><?php endif; ?>
            
            <div class="mb-3">
                <label class="form-label">Nome do Departamento *</label>
                <input type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($departamento['nome'] ?? '') ?>">
            </div>

            <div class="mb-4">
                <label class="form-label d-block">Almoxarifados Permitidos</label>
                <div class="border rounded p-3 bg-light">
                    <?php foreach ($almoxarifados as $a): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="almoxarifados[]" value="<?= $a['id'] ?>" 
                                   id="almox_<?= $a['id'] ?>" <?= in_array($a['id'], $almoxSelecionados) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="almox_<?= $a['id'] ?>">
                                <?= htmlspecialchars($a['nome']) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <small class="text-muted">Marque todos se este for o departamento da Administração.</small>
            </div>

            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="?controller=departamento&action=list" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>