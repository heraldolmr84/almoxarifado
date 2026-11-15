<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Painel de Controle</h1>

    <a href="?controller=dashboard&action=exportar" class="btn btn-outline-success">
        📊 Exportar Estoque
    </a>
</div>

<div class="row mb-4">
    <!-- Card Produtos -->
    <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
        <div class="card text-white bg-primary shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-title text-uppercase">Produtos Ativos</h6>
                <h2 class="display-5 fw-bold mb-2">
                    <?= $totalProdutos ?>
                </h2>
                <small class="card-text">Itens cadastrados no catálogo.</small>
            </div>
        </div>
    </div>

    <!-- Card Pendentes -->
    <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
        <div class="card text-dark bg-warning shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-title text-uppercase">Aguardando Aprovação</h6>
                <h2 class="display-5 fw-bold mb-2">
                    <?= $reqPendentes ?>
                </h2>
                <small class="card-text">Requisições esperando o seu aval.</small>
            </div>
        </div>
    </div>

    <!-- Card Aprovadas -->
    <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
        <div class="card text-white bg-success shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-title text-uppercase">Prontas para Entrega</h6>
                <h2 class="display-5 fw-bold mb-2">
                    <?= $reqAprovadas ?>
                </h2>
                <small class="card-text">Requisições aprovadas aguardando retirada.</small>
            </div>
        </div>
    </div>

    <!-- NOVO Card Backorders (Clicável) -->
    <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
        <!-- d-block e h-100 garantem que o link ocupe todo o espaço da coluna -->
        <a href="?controller=requisicao&action=backorders" class="text-decoration-none h-100 d-block">
            <div class="card text-white <?= $totalBackorders > 0 ? 'bg-danger' : 'bg-secondary' ?> shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title text-uppercase">Itens em Backorder</h6>
                    <h2 class="display-5 fw-bold mb-2">
                        <?= $totalBackorders ?>
                    </h2>
                    <small class="card-text">Itens pendentes de entrega.</small>
                </div>
            </div>
        </a>
    </div>
</div>

<?php
// Verifica se existem itens no array de estoque baixo
$temAlerta = !empty($estoqueBaixo);
?>

<div class="row">
    <div class="col-12">
        <!-- Se tiver alerta, borda vermelha. Se não, borda verde -->
        <div class="card shadow-sm <?= $temAlerta ? 'border-danger' : 'border-success' ?>">

            <!-- Cabeçalho muda de cor, ícone e texto -->
            <div class="card-header <?= $temAlerta ? 'bg-danger text-white' : 'bg-success text-white' ?> fw-bold">
                <?php if ($temAlerta): ?>
                    ⚠️ Alerta de Estoque Mínimo
                <?php else: ?>
                    ✅ Status do Estoque
                <?php endif; ?>
            </div>

            <div class="card-body p-0">
                <?php if (!$temAlerta): ?>
                    <!-- Se estiver tudo OK, mostra apenas a mensagem centralizada -->
                    <div class="text-center py-5">
                        <h5 class="text-success mb-0">Tudo certo! Nenhum produto abaixo do estoque mínimo.</h5>
                    </div>
                <?php else: ?>
                    <!-- Se houver alerta, renderiza a tabela normal -->
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
                            <?php foreach ($estoqueBaixo as $produto): ?>
                                <tr>
                                    <td><?= htmlspecialchars($produto['codigo']) ?></td>
                                    <td><?= htmlspecialchars($produto['descricao']) ?></td>
                                    <td class="text-danger fw-bold">
                                        <?= number_format($produto['quantidade_atual'], 3, ',', '.') ?>
                                    </td>
                                    <td><?= number_format($produto['estoque_minimo'], 3, ',', '.') ?></td>
                                    <td><span class="badge bg-danger">Comprar Urgente</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($lotesVencendo)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning text-dark fw-bold">
                    ⏰ Lotes a Vencer (Próximos 30 dias)
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Lote</th>
                                <th>Almoxarifado</th>
                                <th>Validade</th>
                                <th>Qtd</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lotesVencendo as $lote): ?>
                                <tr>
                                    <td><?= htmlspecialchars($lote['descricao']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($lote['numero_lote']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($lote['almoxarifado']) ?></td>
                                    <td class="text-danger fw-bold"><?= date('d/m/Y', strtotime($lote['validade'])) ?></td>
                                    <td><?= number_format($lote['quantidade'], 3, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>