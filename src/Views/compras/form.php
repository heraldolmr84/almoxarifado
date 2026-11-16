<h1>Novo Pedido de Compra</h1>
<div class="card shadow-sm mt-4">
    <div class="card-body">
        <form action="?controller=pedidoCompra&action=store" method="POST">
            <div class="mb-3">
                <label class="form-label">Justificativa da Compra *</label>
                <textarea name="justificativa" class="form-control" rows="2" required placeholder="Ex: Reposição de estoque de papel A4..."></textarea>
            </div>

            <hr>
            <h5>Itens para Comprar</h5>
            
            <div id="itens-container">
                <div class="row mb-2 item-row">
                    <div class="col-md-6">
                        <select name="produto_id[]" class="form-select select2" required>
                            <option value="">Selecione o produto...</option>
                            <?php foreach ($produtos as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['codigo'] . ' - ' . $p['descricao']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="quantidade[]" class="form-control" required placeholder="Qtd">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="valor_unitario[]" class="form-control" placeholder="R$ Unitário (Opcional)">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger w-100" onclick="removerItem(this)">X</button>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm mb-4" onclick="adicionarItem()">+ Adicionar outro produto</button>
            
            <hr>
            <button type="submit" class="btn btn-primary">Salvar Rascunho</button>
            <a href="?controller=pedidoCompra&action=list" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

<script>
// Mesmo script de clonar itens das requisições (pode copiar de lá)
function adicionarItem() {
    const container = document.getElementById('itens-container');
    const row = container.querySelector('.item-row');
    const newRow = row.cloneNode(true);
    newRow.querySelector('select').value = '';
    newRow.querySelectorAll('input').forEach(i => i.value = '');
    container.appendChild(newRow);
    
    // Reinicializa o Select2 no novo item
    const newSelect = newRow.querySelector('select');
    $(newSelect).select2({ theme: 'bootstrap-5', width: '100%' });
}
function removerItem(btn) {
    if (document.querySelectorAll('.item-row').length > 1) btn.closest('.item-row').remove();
}
</script>