<?php use App\Core\Auth;

$user = Auth::user();

$isAdmin = ($user['perfil'] === 'ADMIN');
$isGerente = ($user['perfil'] === 'GERENTE');
$isAlmoxarife = ($user['perfil'] === 'ALMOXARIFE');

// Quem pode aprovar?
$podeAprovar = ($isAdmin || $isGerente);
// Quem pode comprar e receber?
$podeComprarReceber = ($isAdmin || $isAlmoxarife);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Pedido de Compra <?= $pedido['numero'] ?></h2>
    <div>
        <!-- Botão de Enviar para Aprovação (Criador do rascunho) -->
        <?php if ($pedido['status'] === 'RASCUNHO' && $pedido['solicitante_id'] == $user['id']): ?>
            <a href="?controller=pedidoCompra&action=enviar&id=<?= $pedido['id'] ?>" class="btn btn-warning">Enviar p/
                Aprovação</a>
        <?php endif; ?>

        <!-- Botão de Aprovar (Apenas Admin e Gerente) -->
        <?php if ($pedido['status'] === 'AGUARDANDO_APROVACAO' && $podeAprovar): ?>
            <a href="?controller=pedidoCompra&action=aprovar&id=<?= $pedido['id'] ?>" class="btn btn-success">Aprovar
                Pedido</a>
        <?php endif; ?>

        <!-- Botão de Registrar Compra (Apenas Admin e Almoxarife) -->
        <?php if ($pedido['status'] === 'APROVADO' && $podeComprarReceber): ?>
            <a href="?controller=pedidoCompra&action=comprar&id=<?= $pedido['id'] ?>" class="btn btn-primary">Registrar
                Compra</a>
        <?php endif; ?>

        <!-- Botão de Receber (Apenas Admin e Almoxarife) -->
        <?php if (in_array($pedido['status'], ['COMPRADO', 'RECEBIDO_PARCIAL']) && $podeComprarReceber): ?>
            <a href="?controller=pedidoCompra&action=receber&id=<?= $pedido['id'] ?>" class="btn btn-success">
                📦 Receber Mercadoria
                <?= $pedido['status'] === 'RECEBIDO_PARCIAL' ? 'Restante' : '' ?>
            </a>
        <?php endif; ?>

        <!-- Botão de Imprimir (Disponível para todos que podem ver o pedido já recebido) -->
        <?php if (in_array($pedido['status'], ['RECEBIDO', 'RECEBIDO_PARCIAL'])): ?>
            <a href="?controller=pedidoCompra&action=print&id=<?= $pedido['id'] ?>" target="_blank"
                class="btn btn-outline-dark">
                🖨️ Imprimir Comprovante
            </a>
        <?php endif; ?>

        <a href="?controller=pedidoCompra&action=list" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <p><strong>Status:</strong> <span class="badge bg-primary"><?= $pedido['status'] ?></span></p>
        <p><strong>Solicitante:</strong> <?= htmlspecialchars($pedido['solicitante']) ?></p>
        <p><strong>Justificativa:</strong> <?= nl2br(htmlspecialchars($pedido['justificativa'])) ?></p>

        <?php if ($pedido['documento_compra']): ?>
            <p><strong>Documento da Compra (NF):</strong> <?= htmlspecialchars($pedido['documento_compra']) ?></p>
        <?php endif; ?>
    </div>
</div>

<h4>Itens do Pedido</h4>
<div class="card shadow-sm">
    <table class="table table-striped mb-0">
        <thead class="table-dark">
            <tr>
                <th>Produto</th>
                <th>Qtd Solicitada</th>
                <th>Valor Unitário</th>
                <th>Recebido</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedido['itens'] as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['codigo'] . ' - ' . $item['descricao']) ?></td>
                    <td><?= number_format($item['quantidade'], 2, ',', '.') ?>     <?= $item['unidade'] ?></td>
                    <td><?= $item['valor_unitario'] ? 'R$ ' . number_format($item['valor_unitario'], 2, ',', '.') : '—' ?>
                    </td>
                    <td><?= number_format($item['quantidade_recebida'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>