</main>

<!-- INÍCIO DO RODAPÉ -->
<?php if (isset($user) && $user): ?>
    <!-- Fecha a div principal (que foi aberta no header) -->
    <?php
    // Evita fazer duas consultas seguidas se a variável já existir
    if (!isset($dbConfig)) {
        $dbConfig = (new \App\Models\Configuracao())->get();
    }
    $nomeEmpresa = !empty($dbConfig['nome_fantasia']) ? $dbConfig['nome_fantasia'] : ($dbConfig['razao_social'] ?? 'SISGEM - Sistema de Gestão de Materiais');
    ?>
    <footer class="mt-auto pt-4 pb-2 text-center text-muted small border-top">
        <?= htmlspecialchars($nomeEmpresa) ?> | Desenvolvido com SISGEM
    </footer>
    </div> <!-- Fecha .main-content -->
<?php else: ?>
    <!-- Fecha o container da tela de login -->
    </div>
<?php endif; ?>

<script src="assets/js/bootstrap.bundle.min.js"></script>

<!-- jQuery (necessário para o Select2) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- jQuery Mask Plugin (Para CNPJ, CPF, Telefone) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Inicializador automático do Select2 -->
<script>
    $(document).ready(function () {
        // Aplica o Select2 em qualquer select que tenha a classe "select2"
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Selecione uma opção...',
            allowClear: true
        });

        // 1. REGRA ESPECÍFICA: Select de Fornecedor com criação de tags
        if ($('#selectFornecedorEntrada').length > 0) {
            $('#selectFornecedorEntrada').select2({
                theme: 'bootstrap-5',
                width: '100%',
                tags: true, // <-- O SEGREDO AQUI
                placeholder: 'Selecione ou digite um novo...',
                allowClear: true,
                language: {
                    "noResults": function () {
                        return "Nenhum fornecedor. Pressione Enter para cadastrar.";
                    }
                }
            });
        }


        // 2. LÓGICA DO SKU
        if ($('#selectCategoria').length > 0) {
            // Escuta os dois tipos de eventos para garantir
            $('#selectCategoria').on('change select2:select', function (e) {
                const categoriaId = $(this).val();
                const campoSku = $('#campoSku');

                if (!categoriaId) {
                    campoSku.val('');
                    return;
                }

                campoSku.val('Gerando...');

                fetch('?controller=produto&action=apiProximoSku&categoria_id=' + categoriaId)
                    .then(response => response.text())
                    .then(sku => {
                        campoSku.val(sku.trim());
                    });
            });
        }
    });
</script>
</body>

</html>