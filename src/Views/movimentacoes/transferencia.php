<?php if (isset($mensagem) && $mensagem): ?>
    <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show">
        <?= htmlspecialchars($mensagem['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<h1>Transferência entre Almoxarifados</h1>
<div class="card shadow-sm mt-4 border-info">
    <div class="card-body">
        <form action="?controller=movimentacao&action=storeTransferencia" method="POST">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label text-danger fw-bold">Almoxarifado de Origem *</label>
                    <select name="almoxarifado_origem_id" class="form-select" required>
                        <option value="">De onde vai sair?</option>
                        <?php foreach ($almoxarifados as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-success fw-bold">Almoxarifado de Destino *</label>
                    <select name="almoxarifado_destino_id" class="form-select" required>
                        <option value="">Para onde vai?</option>
                        <?php foreach ($almoxarifados as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-8">
                    <label class="form-label">Produto *</label>
                    <select name="produto_id" class="form-select select2" required>
                        <option value="">Selecione o produto...</option>
                        <?php foreach ($produtos as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['codigo'] . ' - ' . $p['descricao']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Quantidade *</label>
                    <input type="number" step="0.001" name="quantidade" class="form-control" required min="0.001">
                </div>
            </div>

            <div class="alert alert-info py-2">
                <small><strong>Atenção:</strong> Esta ação dará baixa no estoque de origem e dará entrada no estoque de destino simultaneamente.</small>
            </div>

            <button type="submit" class="btn btn-info text-white">Confirmar Transferência</button>
            <a href="?controller=movimentacao&action=list" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>