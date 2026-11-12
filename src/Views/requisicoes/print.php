<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Requisição <?= $requisicao['numero'] ?></title>
    <link href="/assets/boostrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fff; font-size: 14px; }
        .header-print { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { max-height: 60px; }
        .assinatura { margin-top: 50px; border-top: 1px solid #000; width: 300px; text-align: center; padding-top: 5px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <!-- Botão de Imprimir (não sai no papel) -->
    <div class="mb-4 no-print">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir / Salvar PDF</button>
        <a href="?controller=requisicao&action=show&id=<?= $requisicao['id'] ?>" class="btn btn-secondary">Voltar</a>
    </div>

    <!-- Cabeçalho -->
    <div class="row header-print align-items-center">
        <div class="col-2">
            <img src="assets/img/logo_facom.jpg" class="logo" alt="Logo">
        </div>
        <div class="col-8 text-center">
            <h4 class="mb-0">REQUISIÇÃO DE MATERIAL</h4>
            <strong>Nº <?= $requisicao['numero'] ?></strong>
        </div>
        <div class="col-2 text-end">
            <small>Data: <?= date('d/m/Y', strtotime($requisicao['criado_em'])) ?></small>
        </div>
    </div>

    <!-- Dados da Requisição -->
    <div class="row mb-4">
        <div class="col-6">
            <strong>Solicitante:</strong> <?= htmlspecialchars($requisicao['solicitante']) ?><br>
            <strong>Departamento:</strong> <?= htmlspecialchars($requisicao['departamento']) ?>
        </div>
        <div class="col-6">
            <strong>Almoxarifado:</strong> <?= htmlspecialchars($requisicao['almoxarifado']) ?><br>
            <strong>Prioridade:</strong> <?= $requisicao['prioridade'] ?>
        </div>
    </div>

    <div class="mb-4">
        <strong>Justificativa:</strong><br>
        <?= nl2br(htmlspecialchars($requisicao['justificativa'])) ?>
    </div>

    <!-- Tabela de Itens -->
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th width="15%">Código</th>
                <th width="45%">Produto</th>
                <th width="20%">Qtd Solicitada</th>
                <th width="20%">Qtd Entregue</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requisicao['itens'] as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['codigo']) ?></td>
                <td><?= htmlspecialchars($item['descricao']) ?></td>
                <td><?= number_format($item['quantidade_solicitada'], 2, ',', '.') ?> <?= $item['unidade'] ?></td>
                <td><?= $item['quantidade_atendida'] > 0 ? number_format($item['quantidade_atendida'], 2, ',', '.') . ' ' . $item['unidade'] : '' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Assinaturas -->
    <div class="row mt-5 pt-5">
        <div class="col-6 text-center">
            <div class="assinatura mx-auto">
                Assinatura do Solicitante
            </div>
        </div>
        <div class="col-6 text-center">
            <div class="assinatura mx-auto">
                Assinatura do Almoxarife
            </div>
        </div>
    </div>
</div>

</body>
</html>