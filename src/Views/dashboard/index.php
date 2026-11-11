<h1 class="mb-4">Painel de Controle</h1>

<div class="row mb-4">
    <!-- Card Produtos -->
    <div class="col-md-4">
        <div class="card text-white bg-primary shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title">Produtos Ativos</h5>
                <h2 class="display-4 fw-bold"><?= $totalProdutos ?></h2>
                <p class="card-text mb-0">Itens cadastrados no catálogo.</p>
            </div>
        </div>
    </div>

    <!-- Card Pendentes -->
    <div class="col-md-4">
        <div class="card text-dark bg-warning shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title">Aguardando Aprovação</h5>
                <h2 class="display-4 fw-bold"><?= $reqPendentes ?></h2>
                <p class="card-text mb-0">Requisições esperando o seu aval.</p>
            </div>
        </div>
    </div>

    <!-- Card Aprovadas -->
    <div class="col-md-4">
        <div class="card text-white bg-success shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title">Prontas para Entrega</h5>
                <h2 class="display-4 fw-bold"><?= $reqAprovadas ?></h2>
                <p class="card-text mb-0">Requisições aprovadas aguardando retirada.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-danger">
            <div class="card-header bg-danger text-white fw-bold">
                ⚠️ Alerta de Estoque Mínimo
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Produto</th>
                            <th>Estoque Atual</th>
                            <th>Estoque Mínimo</th>
                            <th>Situação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($estoqueBaixo)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-success">
                                    <strong>Tudo certo! Nenhum produto abaixo do estoque mínimo.</strong>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($estoqueBaixo as $produto): ?>
                        <tr>
                            <td><?= htmlspecialchars($produto['codigo']) ?></td>
                            <td><?= htmlspecialchars($produto['descricao']) ?></td>
                            <td class="text-danger fw-bold"><?= number_format($produto['quantidade_atual'], 3, ',', '.') ?></td>
                            <td><?= number_format($produto['estoque_minimo'], 3, ',', '.') ?></td>
                            <td><span class="badge bg-danger">Comprar Urgente</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>