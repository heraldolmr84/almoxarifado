<h1>Registrar Compra Efetuada</h1>
<div class="card shadow-sm mt-4">
    <div class="card-body">
        <div class="alert alert-info">Pedido <?= $pedido['numero'] ?> aprovado. Informe abaixo o documento da compra (Nota Fiscal ou Pedido assinado).</div>
        <form action="?controller=pedidoCompra&action=salvarCompra" method="POST">
            <input type="hidden" name="id" value="<?= $pedido['id'] ?>">
            <div class="mb-4">
                <label class="form-label">Número da Nota Fiscal / Documento *</label>
                <input type="text" name="documento_compra" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Confirmar Compra</button>
            <a href="?controller=pedidoCompra&action=show&id=<?= $pedido['id'] ?>" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>