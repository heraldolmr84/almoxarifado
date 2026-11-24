<?php
$isEdit = isset($fornecedor);
$action = $isEdit ? '?controller=fornecedor&action=update' : '?controller=fornecedor&action=store';
?>
<h1><?= $isEdit ? 'Editar Fornecedor' : 'Novo Fornecedor' ?></h1>

<!-- BLOCO DE EXIBIÇÃO DE MENSAGEM -->
<?php if (isset($mensagem) && $mensagem): ?>
    <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show mt-3">
        <?= htmlspecialchars($mensagem['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm mt-4">
    <div class="card-body">
        <form action="<?= $action ?>" method="POST">
            <?= \App\Core\Csrf::input() ?>
            <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $fornecedor['id'] ?>"><?php endif; ?>

            <div class="row mb-3">
                <div class="col-md-8">
                    <label class="form-label">Razão Social *</label>
                    <input type="text" name="razao_social" class="form-control" required
                        value="<?= htmlspecialchars($fornecedor['razao_social'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">CNPJ / CPF</label>
                    <input type="text" name="cnpj" class="form-control" id="cnpj"
                        value="<?= htmlspecialchars($fornecedor['cnpj'] ?? '') ?>" placeholder="00.000.000/0000-00">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Nome Fantasia</label>
                <input type="text" name="nome_fantasia" class="form-control"
                    value="<?= htmlspecialchars($fornecedor['nome_fantasia'] ?? '') ?>">
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control"
                        value="<?= htmlspecialchars($fornecedor['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="telefone" class="form-control" id="telefone"
                        value="<?= htmlspecialchars($fornecedor['telefone'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="?controller=fornecedor&action=list" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Máscara dinâmica para CNPJ/CPF
        var options = {
            onKeyPress: function (val, e, field, options) {
                var masks = ['000.000.000-00', '00.000.000/0000-00'];
                var mask = (val.length > 14) ? masks[1] : masks[0];
                $('#cnpj').mask(mask, options);
            }
        };
        
        $('#cnpj').mask('000.000.000-00', options);

        // Máscara para Telefone (Fixo e Celular)
        var telOptions = {
            onKeyPress: function (val, e, field, options) {
                var masks = ['(00) 0000-0000', '(00) 00000-0000'];
                var mask = (val.length > 14) ? masks[1] : masks[0];
                $('#telefone').mask(mask, options);
            }
        };
        $('#telefone').mask('(00) 0000-0000', telOptions);
    });
</script>