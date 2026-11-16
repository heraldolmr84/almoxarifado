<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Comprovante de Recebimento - <?= $pedido['numero'] ?></title>
    <link href="/assets/boostrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fff; font-size: 13px; font-family: Arial, sans-serif; }
        .header-print { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { max-height: 60px; }
        .assinatura { margin-top: 60px; border-top: 1px solid #000; width: 250px; text-align: center; padding-top: 5px; margin: 0 auto; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <!-- Botão de Imprimir (não sai no papel) -->
    <div class="mb-4 no-print">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir / Salvar PDF</button>
        <a href="?controller=pedidocompra&action=show&id=<?= $pedido['id'] ?>" class="btn btn-secondary">Voltar</a>
    </div>

    <!-- Cabeçalho -->
    <div class="row header-print align-items-center">
        <div class="col-2">
            <img src="/assets/img/logo_ufms.png" class="logo" alt="Logo">
        </div>
        <div class="col-8 text-center">
            <h4 class="mb-0">COMPROVANTE DE RECEBIMENTO DE MATERIAL</h4>
            <strong>Pedido de Compra: <?= $pedido['numero'] ?></strong>
        </div>
        <div class="col-2 text-end">
            <small>Data: <?= date('d/m/Y', strtotime($pedido['data_compra'] ?? $pedido['data_criacao'])) ?></small>
        </div>
    </div>

    <!-- Dados da Compra -->
    <div class="row mb-3">
        <div class="col-6">
            <strong>Fornecedor:</strong> <?= htmlspecialchars($pedido['fornecedor_nome'] ?? 'Não informado') ?><br>
            <strong>Documento (NF):</strong> <?= htmlspecialchars($pedido['documento_compra'] ?? '—') ?><br>
            <strong>Almoxarifado Destino:</strong> <?= htmlspecialchars($pedido['almoxarifado_nome'] ?? '—') ?>
        </div>
        <div class="col-6">
            <strong>Solicitante (Comprador):</strong> <?= htmlspecialchars($pedido['solicitante']) ?><br>
            <strong>Aprovador:</strong> <?= htmlspecialchars($pedido['aprovador'] ?? '—') ?><br>
            <strong>Justificativa:</strong> <?= htmlspecialchars($pedido['justificativa']) ?>
        </div>
    </div>

    <!-- Tabela de Itens -->
    <table class="table table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th width="15%">Código</th>
                <th width="45%">Produto</th>
                <th width="15%" class="text-end">Qtd Pedida</th>
                <th width="15%" class="text-end">Qtd Recebida</th>
                <th width="10%">Unid.</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedido['itens'] as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['codigo']) ?></td>
                <td><?= htmlspecialchars($item['descricao']) ?></td>
                <td class="text-end"><?= number_format($item['quantidade'], 2, ',', '.') ?></td>
                <td class="text-end fw-bold"><?= number_format($item['quantidade_recebida'], 2, ',', '.') ?></td>
                <td><?= $item['unidade'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Assinaturas -->
    <br><br><br><br><br>
    <div class="row mt-5 pt-5">
        <div class="col-4 text-center">
            <div class="assinatura">
                Comprador (Solicitante)<br>
                <small class="text-muted"><?= htmlspecialchars($pedido['solicitante']) ?></small>
            </div>
        </div>
        <br><br>
        <div class="col-4 text-center">
            <div class="assinatura">
                Aprovador (Supervisor)<br>
                <small class="text-muted"><?= htmlspecialchars($pedido['aprovador'] ?? '') ?></small>
            </div>
        </div>
        <br><br>
        <div class="col-4 text-center">
            <div class="assinatura">
                Recebedor (Almoxarife)<br>
                <small class="text-muted">Assinatura e Carimbo</small>
            </div>
        </div>
    </div>
</div>

</body>
</html>