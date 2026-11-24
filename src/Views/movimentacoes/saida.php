<?php if (isset($mensagem) && $mensagem): ?>
    <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show">
        <?= htmlspecialchars($mensagem['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<h1>Nova Saída Direta</h1>
<div class="card shadow-sm mt-4">
    <div class="card-body">
        <form action="?controller=movimentacao&action=storeSaida" method="POST">
            <?= \App\Core\Csrf::input() ?>

            <div class="row mb-3">
                <!-- Produto e Almoxarifado com onchange para recarregar a página -->
                <div class="col-md-4">
                    <label class="form-label">Produto *</label>
                    <select name="produto_id" class="form-select select2" required
                        onchange="location.href='?controller=movimentacao&action=saida&produto_id=' + this.value + '&almoxarifado_id=' + document.getElementById('almoxSelect').value;">
                        <option value="">Selecione...</option>
                        <?php foreach ($produtos as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $produtoSelecionado == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['codigo'] . ' - ' . $p['descricao']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Almoxarifado *</label>
                    <select name="almoxarifado_id" id="almoxSelect" class="form-select" required
                        onchange="location.href='?controller=movimentacao&action=saida&produto_id=' + document.querySelector('[name=produto_id]').value + '&almoxarifado_id=' + this.value;">
                        <?php foreach ($almoxarifados as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= $almoxarifadoSelecionado == $a['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Lote: Só aparece se houver lotes disponíveis -->
                <div class="col-md-4">
                    <label class="form-label">Lote / Validade *</label>
                    <?php if (!empty($lotes)): ?>
                        <select name="lote_id" class="form-select" required>
                            <option value="">Selecione o Lote...</option>
                            <?php foreach ($lotes as $l): ?>
                                <option value="<?= $l['id'] ?>">
                                    Lote:
                                    <?= htmlspecialchars($l['numero_lote']) ?>
                                    (Val:
                                    <?= $l['validade'] ? date('d/m/Y', strtotime($l['validade'])) : 'N/A' ?>)
                                    - Saldo:
                                    <?= $l['quantidade'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="text" class="form-control" value="Selecione Produto/Almoxarifado" disabled>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Motivo (Subtipo) *</label>
                    <select name="subtipo" class="form-select" required>
                        <option value="CONSUMO">Consumo Interno</option>
                        <option value="PERDA">Perda / Avaria</option>
                        <option value="VENCIMENTO">Vencimento</option>
                        <option value="DOACAO">Doação</option>
                        <option value="AJUSTE">Ajuste de Inventário</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Quantidade *</label>
                    <input type="number" step="0.001" name="quantidade" class="form-control" required min="0.001">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Documento (Opcional)</label>
                    <input type="text" name="documento" class="form-control" maxlength="50">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Observação / Justificativa</label>
                <textarea name="observacao" class="form-control" rows="2"></textarea>
            </div>

            <button type="submit" class="btn btn-danger">Confirmar Saída</button>
            <a href="?controller=movimentacao&action=list" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>