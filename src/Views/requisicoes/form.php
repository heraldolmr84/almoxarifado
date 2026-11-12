<?php if (isset($mensagem) && $mensagem): ?>
    <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show">
        <?= htmlspecialchars($mensagem['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<h1>Nova Requisição</h1>
<div class="card shadow-sm mt-4">
    <div class="card-body">
        <form action="?controller=requisicao&action=store" method="POST">

            <!-- Cabeçalho -->
            <div class="row mb-3">
                <!-- Campo de Departamento foi removido. O sistema sabe quem é o usuário logado. -->

                <div class="col-md-6">
                    <label class="form-label">Almoxarifado de Origem *</label>
                    <select name="almoxarifado_id" class="form-select" required <?= count($almoxarifadosDisponiveis) <= 1 ? 'disabled' : '' ?>
                        onchange="location.href='?controller=requisicao&action=create&almoxarifado_id=' + this.value;">
                        <option value="">Selecione o Almoxarifado...</option>
                        <?php foreach ($almoxarifadosDisponiveis as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= $almoxarifadoSelecionado == $a['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (count($almoxarifadosDisponiveis) <= 1 && $almoxarifadoSelecionado > 0): ?>
                        <input type="hidden" name="almoxarifado_id" value="<?= $almoxarifadoSelecionado ?>">
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Prioridade *</label>
                    <select name="prioridade" class="form-select" required>
                        <option value="NORMAL">Normal</option>
                        <option value="ALTA">Alta</option>
                        <option value="URGENTE">Urgente</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Justificativa *</label>
                <textarea name="justificativa" class="form-control" rows="2" required
                    placeholder="Ex: Reparo da esteira 04..."></textarea>
            </div>

            <hr>
            <h5>Itens da Requisição</h5>

            <!-- Lista de Itens -->
            <div id="itens-container">
                <div class="row mb-2 item-row">
                    <div class="col-md-8">
                        <select name="produto_id[]" class="form-select" required <?= empty($produtosDisponiveis) ? 'disabled' : '' ?>>
                            <option value="">Selecione o produto...</option>
                            <?php foreach ($produtosDisponiveis as $p): ?>
                                <option value="<?= $p['id'] ?>">
                                    <?= htmlspecialchars($p['codigo'] . ' - ' . $p['descricao']) ?> (Saldo:
                                    <?= $p['quantidade'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.001" name="quantidade[]" class="form-control" required min="0.001"
                            placeholder="Qtd">
                    </div>
                    <div class="col-md-1">
                        <!-- Botão de remover item (inativo no primeiro por padrão) -->
                        <button type="button" class="btn btn-danger w-100" onclick="removerItem(this)">X</button>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm mb-4" onclick="adicionarItem()">+ Adicionar
                outro produto</button>

            <hr>
            <button type="submit" class="btn btn-primary">Salvar Rascunho</button>
            <a href="?controller=requisicao&action=list" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

<!-- Script para adicionar/remover linhas de produtos dinamicamente -->
<script>
    function adicionarItem() {
        const container = document.getElementById('itens-container');
        const row = container.querySelector('.item-row');
        const newRow = row.cloneNode(true);

        // Limpa os valores do clone
        newRow.querySelector('select').value = '';
        newRow.querySelector('input').value = '';

        container.appendChild(newRow);
    }

    function removerItem(btn) {
        const container = document.getElementById('itens-container');
        // Só remove se houver mais de uma linha
        if (container.querySelectorAll('.item-row').length > 1) {
            btn.closest('.item-row').remove();
        } else {
            alert('A requisição precisa ter pelo menos um item.');
        }
    }
</script>