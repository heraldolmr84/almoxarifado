<h1 class="mb-4">Painel Gerencial</h1>

<!-- FORMULÁRIO DE FILTRO BI -->
<div class="card shadow-sm mb-4 border-0 bg-light">
    <div class="card-body py-3">
        <form method="GET" action="" class="row gx-3 align-items-end"> <!-- MUDOU PARA align-items-end -->
            <input type="hidden" name="controller" value="dashboard">
            <input type="hidden" name="action" value="gerencial">

            <div class="col-md-3">
                <label class="form-label mb-1 small text-muted fw-bold">Data Inicial</label>
                <input type="date" name="data_inicio" class="form-control form-control-sm"
                    value="<?= $filtros['data_inicio'] ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1 small text-muted fw-bold">Data Final</label>
                <input type="date" name="data_fim" class="form-control form-control-sm"
                    value="<?= $filtros['data_fim'] ?>">
            </div>
            <div class="col-md-3">
                <!-- Botão formatado para ter a mesma altura do input -->
                <button type="submit"
                    class="btn btn-primary btn-sm w-100 d-flex justify-content-center align-items-center"
                    style="background-color: #0088B7; border:none; height: 31px;">
                    <i class="bi bi-funnel me-1"></i> Aplicar Filtro
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Cards Superiores (3 colunas padronizadas) -->
<div class="row mb-4">
    <!-- Card 1: Gastos -->
    <div class="col-md-4 mb-3">
        <div class="card text-white shadow-sm border-0 h-100" style="background-color: #0088B7;">
            <div class="card-body">
                <h6 class="card-title text-uppercase text-white-50">Gasto no Período</h6>
                <h2 class="display-5 fw-bold mb-0">R$
                    <?= number_format($totalGastoPeriodo, 2, ',', '.') ?>
                </h2>
            </div>
        </div>
    </div>

    <!-- Card 2: Catálogo -->
    <div class="col-md-4 mb-3">
        <div class="card text-dark bg-light shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="card-title text-uppercase text-muted">Catálogo de Materiais</h6>
                <h2 class="display-5 fw-bold mb-0">
                    <?= $totalProdutos ?>
                </h2>
                <small>Produtos ativos</small>
            </div>
        </div>
    </div>

    <!-- Card 3: Movimentações no Período -->
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-dark shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="card-title text-uppercase text-white-50">Movimentações no Período</h6>
                <?php
                $totalMov = 0;
                if (!empty($graficoMovimentacoes['entradas'])) {
                    // Soma todas as entradas e saídas do período filtrado
                    $totalMov = array_sum($graficoMovimentacoes['entradas']) + array_sum($graficoMovimentacoes['saidas']);
                }
                ?>
                <h2 class="display-5 fw-bold mb-0">
                    <?= number_format($totalMov, 0, ',', '.') ?>
                </h2>
                <small>Itens movimentados</small>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row">
    <!-- Gráfico de Evolução de Compras -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold">Evolução de Compras (R$)</div>
            <div class="card-body">
                <div style="position: relative; height: 200px; width: 100%;">
                    <canvas id="graficoCompras"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Consumo por Setor -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold">Top 5 Setores (Consumo)</div>
            <div class="card-body">
                <div style="position: relative; height: 200px; width: 100%;">
                    <canvas id="graficoDepartamentos"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- NOVO: Gráfico de Entradas e Saídas -->
    <div class="col-12 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold">Fluxo de Estoque: Entradas x Saídas (Quantidade)</div>
            <div class="card-body">
                <div style="position: relative; height: 200px; width: 100%;">
                    <canvas id="graficoMovimentacoes"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Importando Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Aguarda o documento carregar completamente antes de renderizar
    document.addEventListener("DOMContentLoaded", function () {

        // Cores padrão do sistema
        const corAzul = '#0088B7';
        const corVerde = '#198754';
        const corVermelho = '#dc3545';

        // --- 1. Gráfico de Compras (Linhas) ---
        const labelsCompras = <?= json_encode(array_column($graficoMeses ?? [], 'mes')) ?>;
        const dadosCompras = <?= json_encode(array_column($graficoMeses ?? [], 'total')) ?>;

        const ctxCompras = document.getElementById('graficoCompras');
        if (ctxCompras) {
            new Chart(ctxCompras.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labelsCompras,
                    datasets: [{
                        label: 'Total Gasto (R$)',
                        data: dadosCompras,
                        backgroundColor: corAzul, // Usa a cor azul direta
                        borderRadius: 4
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
            });
        }

        // --- 2. Gráfico de Departamentos (Rosca) ---
        const labelsDeptos = <?= json_encode(array_column($graficoDepartamentos ?? [], 'departamento')) ?>;
        const dadosDeptos = <?= json_encode(array_column($graficoDepartamentos ?? [], 'total')) ?>;

        const ctxDeptos = document.getElementById('graficoDepartamentos');
        if (ctxDeptos) {
            new Chart(ctxDeptos.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: labelsDeptos,
                    datasets: [{
                        data: dadosDeptos,
                        backgroundColor: [corAzul, corVerde, '#ffc107', corVermelho, '#6c757d'],
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });
        }

        // --- 3. Gráfico de Entradas x Saídas (Barras) ---
        const labelsMov = <?= json_encode($graficoMovimentacoes['meses'] ?? []) ?>;
        const dadosEntradas = <?= json_encode($graficoMovimentacoes['entradas'] ?? []) ?>;
        const dadosSaidas = <?= json_encode($graficoMovimentacoes['saidas'] ?? []) ?>;

        const ctxMov = document.getElementById('graficoMovimentacoes');
        if (ctxMov) {
            new Chart(ctxMov.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labelsMov,
                    datasets: [
                        {
                            label: 'Entradas (Qtd)',
                            data: dadosEntradas,
                            backgroundColor: corVerde,
                            borderRadius: 4
                        },
                        {
                            label: 'Saídas (Qtd)',
                            data: dadosSaidas,
                            backgroundColor: corVermelho,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true }, x: { grid: { display: false } } },
                    plugins: { legend: { position: 'top' } }
                }
            });
        }
    });
</script>