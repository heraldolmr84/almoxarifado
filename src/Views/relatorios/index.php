<h1 class="mb-4">Central de Relatórios</h1>
<div class="row g-4">
    <!-- Relatório de Movimentações -->
    <div class="col-md-4">
        <div class="card shadow-sm h-100 text-center p-4">
            <h2 class="display-4">📄</h2>
            <h5 class="card-title">Extrato de Movimentações</h5>
            <p class="text-muted small">Todas as entradas e saídas do estoque em um período.</p>
            <a href="?controller=relatorio&action=movimentacoes" class="btn btn-primary mt-auto">Acessar Relatório</a>
        </div>
    </div>

    <!-- Relatório de Consumo -->
    <div class="col-md-4">
        <div class="card shadow-sm h-100 text-center p-4">
            <h2 class="display-4">🏢</h2>
            <h5 class="card-title">Consumo por Setor</h5>
            <p class="text-muted small">Volume de materiais retirados por cada departamento.</p>
            <a href="?controller=relatorio&action=consumo" class="btn btn-primary mt-auto">Acessar Relatório</a>
        </div>
    </div>

    <!-- Relatório de Estoque Parado -->
    <div class="col-md-4">
        <div class="card shadow-sm h-100 text-center p-4">
            <h2 class="display-4">🐢</h2>
            <h5 class="card-title">Estoque Parado</h5>
            <p class="text-muted small">Produtos que não tiveram nenhuma movimentação recente.</p>
            <a href="?controller=relatorio&action=semMovimentacao" class="btn btn-primary mt-auto">Acessar Relatório</a>
        </div>
    </div>

    <!-- Relatório de Compras por Período -->
    <div class="col-md-4">
        <div class="card shadow-sm h-100 text-center p-4">
            <h2 class="display-4">🛒</h2>
            <h5 class="card-title">Compras por Período</h5>
            <p class="text-muted small">Valores totais de todas as compras efetuadas em um período.</p>
            <a href="?controller=relatorio&action=compras" class="btn btn-primary mt-auto">Acessar Relatório</a>
        </div>
    </div>

    <!-- Relatório de Materiais Comprados -->
    <div class="col-md-4">
        <div class="card shadow-sm h-100 text-center p-4">
            <h2 class="display-4">📦</h2>
            <h5 class="card-title">Materiais Comprados</h5>
            <p class="text-muted small">Detalhamento de todos os itens adquiridos por período e produto.</p>
            <a href="?controller=relatorio&action=materiaisComprados" class="btn btn-primary mt-auto">Acessar
                Relatório</a>
        </div>
    </div>
</div>