<h1>Conferência de Recebimento - <?= $pedido['numero'] ?></h1>

<div class="card shadow-sm mt-4">
    <div class="card-body">
        <form action="?controller=pedidoCompra&action=storeRecebimento" method="POST">
            <?= \App\Core\Csrf::input() ?>
            <input type="hidden" name="id" value="<?= $pedido['id'] ?>">

            <div class="row mb-4 bg-light p-3 rounded border">
                <div class="col-md-6">
                    <label class="form-label text-primary fw-bold">Almoxarifado de Destino *</label>
                    <select name="almoxarifado_id" class="form-select" required>
                        <option value="">Selecione onde o material será guardado...</option>
                        <?php foreach ($almoxarifados as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-primary fw-bold">Fornecedor Emissor (NF)</label>
                    <select name="fornecedor_id" class="form-select">
                        <option value="">Selecione o fornecedor...</option>
                        <?php foreach ($fornecedores as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['razao_social']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <h5>Itens do Pedido</h5>
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Produto</th>
                        <th>Qtd Pedida</th>
                        <th>Qtd Recebida</th>
                        <th class="bg-success text-white">Receber Agora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedido['itens'] as $item):
                        $pendente = $item['quantidade'] - $item['quantidade_recebida'];
                        ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($item['descricao']) ?>
                                <?php if ($item['controla_lote']): ?>
                                    <span class="badge bg-warning text-dark">Controla Lote</span>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($item['quantidade'], 2, ',', '.') ?></td>
                            <td><?= number_format($item['quantidade_recebida'], 2, ',', '.') ?></td>
                            <td>
                                <?php if ($pendente > 0): ?>
                                    <input type="number" step="0.001" name="qtd_recebida[<?= $item['id'] ?>]"
                                        class="form-control mb-2" value="<?= $pendente ?>" max="<?= $pendente ?>" min="0">

                                    <?php if ($item['controla_lote']): ?>
                                        <div class="row gx-1">
                                            <div class="col-6">
                                                <input type="text" name="lote[<?= $item['id'] ?>]"
                                                    class="form-control form-control-sm" placeholder="Nº Lote">
                                            </div>
                                            <div class="col-6">
                                                <input type="date" name="validade[<?= $item['id'] ?>]"
                                                    class="form-control form-control-sm" title="Validade">
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-success">Completo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <hr>
            <button type="submit" class="btn btn-success">Confirmar Recebimento e Entrar no Estoque</button>
            <a href="?controller=pedidoCompra&action=show&id=<?= $pedido['id'] ?>"
                class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>