<h1 class="mb-4">Painel Gerencial</h1>

<!-- Cards Superiores (3 colunas padronizadas) -->
<div class="row mb-4">
    <!-- Card 1: Gastos -->
    <div class="col-md-4 mb-3">
        <div class="card text-white shadow-sm border-0 h-100" style="background-color: #0088B7;">
            <div class="card-body">
                <h6 class="card-title text-uppercase text-white-50">Gasto em Compras (Mês Atual)</h6>
                <h2 class="display-5 fw-bold mb-0">R$ <?= number_format($totalGastoMes, 2, ',', '.') ?></h2>
            </div>
        </div>
    </div>

    <!-- Card 2: Catálogo -->
    <div class="col-md-4 mb-3">
        <div class="card text-dark bg-light shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="card-title text-uppercase text-muted">Catálogo de Materiais</h6>
                <h2 class="display-5 fw-bold mb-0"><?= $totalProdutos ?></h2>
                <small>Produtos ativos cadastrados</small>
            </div>
        </div>
    </div>

    <!-- Card 3: Movimentações no Mês -->
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-dark shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="card-title text-uppercase text-white-50">Movimentações (Últimos 6 meses)</h6>
                <?php
                $totalMov = 0;
                // Verifica se as chaves existem e se não estão vazias
                if (!empty($graficoMovimentacoes['entradas']) && !empty($graficoMovimentacoes['saidas'])) {
                    $countEntradas = count($graficoMovimentacoes['entradas']);
                    $countSaidas = count($graficoMovimentacoes['saidas']);

                    // Pega o último item do array sem usar end()
                    $ultimoMesEntradas = $graficoMovimentacoes['entradas'][$countEntradas - 1] ?? 0;
                    $ultimoMesSaidas = $graficoMovimentacoes['saidas'][$countSaidas - 1] ?? 0;

                    $totalMov = $ultimoMesEntradas + $ultimoMesSaidas;
                }
                ?>
                <h2 class="display-5 fw-bold mb-0">
                    <?= number_format($totalMov, 0, ',', '.') ?>
                </h2>
                <small>Itens movimentados no último mês</small>
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
                type: 'line',
                data: {
                    labels: labelsCompras,
                    datasets: [{
                        label: 'Total Gasto (R$)',
                        data: dadosCompras,
                        borderColor: corAzul,
                        backgroundColor: 'rgba(0, 136, 183, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
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