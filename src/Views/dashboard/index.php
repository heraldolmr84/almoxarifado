<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="poppin">Gestão Eficiente de Materiais</h1>
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
                <h2 class="display-5 fw-bold mb-2" id="num-pendentes">
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
                <h2 class="display-5 fw-bold mb-2" id="num-aprovadas">
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
                    <h2 class="display-5 fw-bold mb-2" id="num-backorders">
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
                    <?php if ($totalPaginasEstoque > 1): ?>
                        <div class="card-footer bg-white d-flex justify-content-end">
                            <nav>
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item <?= $pageEstoque <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link"
                                            href="?<?= http_build_query(array_merge($_GET, ['page_estoque' => $pageEstoque - 1])) ?>">Anterior</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $totalPaginasEstoque; $i++): ?>
                                        <li class="page-item <?= $pageEstoque == $i ? 'active' : '' ?>">
                                            <a class="page-link"
                                                href="?<?= http_build_query(array_merge($_GET, ['page_estoque' => $i])) ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $pageEstoque >= $totalPaginasEstoque ? 'disabled' : '' ?>">
                                        <a class="page-link"
                                            href="?<?= http_build_query(array_merge($_GET, ['page_estoque' => $pageEstoque + 1])) ?>">Próximo</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
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
                    <?php if ($totalPaginasLotes > 1): ?>
                        <div class="card-footer bg-white d-flex justify-content-end">
                            <nav>
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item <?= $pageLotes <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link"
                                            href="?<?= http_build_query(array_merge($_GET, ['page_lotes' => $pageLotes - 1])) ?>">Anterior</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $totalPaginasLotes; $i++): ?>
                                        <li class="page-item <?= $pageLotes == $i ? 'active' : '' ?>">
                                            <a class="page-link"
                                                href="?<?= http_build_query(array_merge($_GET, ['page_lotes' => $i])) ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $pageLotes >= $totalPaginasLotes ? 'disabled' : '' ?>">
                                        <a class="page-link"
                                            href="?<?= http_build_query(array_merge($_GET, ['page_lotes' => $pageLotes + 1])) ?>">Próximo</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Container para a notificação flutuante (Toast) -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="toastNotificacao" class="toast align-items-center text-bg-primary border-0" role="alert"
        aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-bell-fill me-2"></i> <strong>Nova Requisição!</strong> Uma nova solicitação aguarda
                aprovação.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
    // Variável para guardar o número atual de requisições (para saber se aumentou)
    let qtdPendentesAtual = parseInt(document.getElementById('num-pendentes').innerText);

    function checarNovasRequisicoes() {
        // Faz a chamada para a nossa API em PHP
        fetch('?controller=dashboard&action=apiStatus')
            .then(response => response.json())
            .then(dados => {
                // Atualiza os números na tela
                document.getElementById('num-aprovadas').innerText = dados.reqAprovadas;

                const cardBackorder = document.getElementById('num-backorders').parentElement;
                document.getElementById('num-backorders').innerText = dados.totalBackorders;

                // Muda a cor do card de backorder se passar de 0
                if (dados.totalBackorders > 0) {
                    cardBackorder.classList.remove('bg-secondary');
                    cardBackorder.classList.add('bg-danger');
                } else {
                    cardBackorder.classList.remove('bg-danger');
                    cardBackorder.classList.add('bg-secondary');
                }

                // Verifica se o número de pendentes AUMENTOU
                if (dados.reqPendentes > qtdPendentesAtual) {
                    // Atualiza o número na tela
                    document.getElementById('num-pendentes').innerText = dados.reqPendentes;

                    // Mostra a notificação (Toast)
                    const toastEl = document.getElementById('toastNotificacao');
                    const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
                    toast.show();

                    // Toca um som de notificação (opcional)
                    tocarSom();
                } else {
                    // Se diminuiu (alguém atendeu), apenas atualiza o número
                    document.getElementById('num-pendentes').innerText = dados.reqPendentes;
                }

                // Atualiza a variável de controle
                qtdPendentesAtual = dados.reqPendentes;
            })
            .catch(error => console.log('Erro ao checar status:', error));
    }

    // Função opcional para tocar um som de "Plim"
    function tocarSom() {
        const audio = new Audio('https://actions.google.com/sounds/v1/alarms/beep_short.ogg');
        audio.play().catch(e => console.log('Áudio bloqueado pelo navegador.'));
    }

    // Executa a checagem a cada 10 segundos (10000 milissegundos)
    setInterval(checarNovasRequisicoes, 10000);
</script>