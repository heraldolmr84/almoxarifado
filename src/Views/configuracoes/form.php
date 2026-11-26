<h1 class="mb-4">Configurações da Empresa</h1>

<?php if (isset($mensagem) && $mensagem): ?>
    <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show">
        <?= htmlspecialchars($mensagem['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <!-- ENCTYPE É VITAL PARA UPLOAD DE ARQUIVOS -->
        <form action="?controller=configuracao&action=update" method="POST" enctype="multipart/form-data">
            <?= \App\Core\Csrf::input() ?>

            <div class="row mb-3">
                <div class="col-md-8">
                    <label class="form-label">Razão Social *</label>
                    <input type="text" name="razao_social" class="form-control" required
                        value="<?= htmlspecialchars($config['razao_social'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">CNPJ</label>
                    <input type="text" name="cnpj" id="cnpj_empresa" class="form-control"
                        value="<?= htmlspecialchars($config['cnpj'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cor Principal do Sistema</label>
                    <!-- Input nativo de paleta de cores -->
                    <input type="color" name="cor_primaria" class="form-control form-control-color"
                        value="<?= htmlspecialchars($config['cor_primaria'] ?? '#0088B7') ?>"
                        title="Escolha a cor da marca">
                </div>
                <div class="col-md-8">
                    <small class="text-muted">Esta cor será aplicada no menu lateral, botões e paginação.</small>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Nome Fantasia</label>
                <input type="text" name="nome_fantasia" class="form-control"
                    value="<?= htmlspecialchars($config['nome_fantasia'] ?? '') ?>">
            </div>

            <div class="row mb-4 align-items-center">
                <div class="col-md-8">
                    <label class="form-label">Logo da Empresa (PNG, JPG ou SVG)</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                </div>
                <div class="col-md-4 text-center">
                    <?php if (!empty($config['logo_path'])): ?>
                        <img src="<?= htmlspecialchars($config['logo_path']) ?>" alt="Logo Atual"
                            style="max-height: 60px; background: #eee; padding: 5px; border-radius: 5px;">
                    <?php else: ?>
                        <span class="text-muted small">Nenhuma logo cadastrada</span>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Salvar Configurações</button>
        </form>
    </div>
</div>

<script>
    // Máscara de CNPJ
    $(document).ready(function () {
        $('#cnpj_empresa').mask('00.000.000/0000-00');
    });
</script>