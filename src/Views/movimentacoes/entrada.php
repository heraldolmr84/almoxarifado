<?php if (isset($mensagem) && $mensagem): ?>
    <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show">
        <?= htmlspecialchars($mensagem['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<h1>Nova Entrada de Estoque</h1>
<div class="card shadow-sm mt-4">
    <div class="card-body">
        <form action="?controller=movimentacao&action=storeEntrada" method="POST">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Produto *</label>
                    <select name="produto_id" class="form-select" required>
                        <option value="">Selecione o produto...</option>
                        <?php foreach ($produtos as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['codigo'] . ' - ' . $p['descricao']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Almoxarifado de Destino *</label>
                    <select name="almoxarifado_id" class="form-select" required>
                        <?php foreach ($almoxarifados as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Quantidade *</label>
                    <input type="number" step="0.001" name="quantidade" class="form-control" required min="0.001">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Valor Unitário (R$)</label>
                    <input type="number" step="0.01" name="valor_unitario" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Documento (NF/Recibo)</label>
                    <input type="text" name="documento" class="form-control" maxlength="50">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Observação</label>
                <textarea name="observacao" class="form-control" rows="2"></textarea>
            </div>

            <button type="submit" class="btn btn-success">Confirmar Entrada</button>
            <a href="?controller=movimentacao&action=list" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>