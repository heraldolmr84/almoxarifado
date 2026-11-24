<?php
$isEdit = isset($produto);
$action = $isEdit ? '?controller=produto&action=update' : '?controller=produto&action=store';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?= $isEdit ? 'Editar Produto' : 'Novo Produto' ?></h1>
    <a href="?controller=produto&action=list" class="btn btn-secondary">Voltar</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?= $action ?>" method="POST">

            <?= \App\Core\Csrf::input() ?>

            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= $produto['id'] ?>">
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Código (SKU)</label>
                    <input type="text" name="codigo" id="campoSku" class="form-control bg-light" readonly
                        value="<?= htmlspecialchars($produto['codigo'] ?? '') ?>"
                        placeholder="Gerado pela categoria...">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Descrição *</label>
                    <input type="text" name="descricao" class="form-control" required maxlength="200"
                        value="<?= htmlspecialchars($produto['descricao'] ?? '') ?>">
                </div>
            </div>

            

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Categoria</label>
                    <select name="categoria_id" id="selectCategoria" class="form-select">
                        <option value="">Selecione...</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($produto['categoria_id']) && $produto['categoria_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Unidade de Medida *</label>
                    <select name="unidade_medida_id" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($unidades as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= (isset($produto['unidade_medida_id']) && $produto['unidade_medida_id'] == $u['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['sigla']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estoque Mínimo</label>
                    <input type="number" step="0.001" name="estoque_minimo" class="form-control"
                        value="<?= htmlspecialchars($produto['estoque_minimo'] ?? '0') ?>">
                </div>
            </div>

            <div class="mb-4 form-check">
                <input type="checkbox" name="controla_lote" value="1" class="form-check-input" id="controlaLote"
                    <?= (isset($produto['controla_lote']) && $produto['controla_lote'] == 't') ? 'checked' : '' ?>>
                <label class="form-check-label" for="controlaLote">Este produto exige controle de lote/validade?</label>
            </div>

            <button type="submit" class="btn btn-primary">
                <?= $isEdit ? 'Atualizar Produto' : 'Salvar Produto' ?>
            </button>
        </form>
    </div>
</div>

<script>
    
</script>