</main>

<!-- INÍCIO DO RODAPÉ -->
<footer class="bg-dark text-light py-3 mt-auto border-top border-secondary">
    <div class="container">
        <div class="row align-items-center">
            <!-- Lado Esquerdo: Nome do Sistema -->
            <div class="col-md-4 text-center text-md-start mb-2 mb-md-0">
                <strong>Sistema de Almoxarifado</strong>
            </div>

            <!-- Centro: Facom -->
            <div class="col-md-4 text-center mb-2 mb-md-0">
                <small>Facom - Faculdade de Computação</small>
            </div>

            <!-- Lado Direito: Versão de Compilação -->
            <div class="col-md-4 text-center text-md-end">
                <small class="text">
                    Versão <?= date('Y.m.d') ?> <!-- Gera a versão baseada na data atual -->
                </small>
            </div>
        </div>
    </div>
</footer>

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
    });
</script>
</body>

</html>