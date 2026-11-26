<!-- Cabeçalho do Relatório -->
<div class="relatorio-header" style="position: relative; width: 100%; min-height: 70px;">

    <!-- Centro: Logo e Nome -->
    <div class="text-center">
        <?php if (!empty($config['logo_path'])): ?>
            <img src="/almoxarifado/public/<?= htmlspecialchars($config['logo_path']) ?>" alt="Logo"
                style="max-height: 45px; margin-bottom: 5px;">
        <?php else: ?>
            <img src="/almoxarifado/public/assets/img/logo_ufms.png" alt="Logo"
                style="max-height: 45px; margin-bottom: 5px;">
        <?php endif; ?>

        <h4 style="margin: 0; font-weight: bold; font-size: 18px;">
            <?= htmlspecialchars($config['nome_fantasia'] ?? $config['razao_social'] ?? 'SISGEM') ?></h4>
        <p style="margin: 2px 0 0 0; font-size: 12px; font-weight: bold; color: #555;">Extrato de Movimentações</p>
    </div>

    <!-- Direita: Usuário e Data (Posicionado Absoluto em relação ao cabeçalho) -->
    <!-- O top: 50px alinha exatamente com o texto do nome da empresa -->
    <div style="position: absolute; right: 0; top: 50px; text-align: right;">
        <p style="margin: 0; font-size: 11px;">
            <strong>Usuário:</strong> <?= htmlspecialchars(\App\Core\Auth::user()['nome'] ?? 'Sistema') ?>
        </p>
        <p style="margin: 2px 0 0 0; font-size: 9px; color: #666;">
            Período: <?= date('d/m/Y', strtotime($filtros['data_inicio'])) ?> a
            <?= date('d/m/Y', strtotime($filtros['data_fim'])) ?><br>
            Emitido em: <?= date('d/m/Y H:i') ?>
        </p>
    </div>

</div>

<!-- Título da tela e botões (não imprimem) -->
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h1>Extrato de Movimentações</h1>
    <div>
        <button onclick="window.print()" class="btn btn-outline-dark me-2">🖨️ Imprimir / PDF</button>
        <a href="?controller=relatorio&action=index" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<!-- Filtros (não imprimem) -->
<div class="card shadow-sm mb-4 no-print">
    <div class="card-body py-3 bg-light">
        <form method="GET" action="" class="row gx-2 align-items-end">
            <input type="hidden" name="controller" value="relatorio">
            <input type="hidden" name="action" value="movimentacoes">
            <div class="col-md-3">
                <label class="form-label mb-1 small">Data Inicial</label>
                <input type="date" name="data_inicio" class="form-control form-control-sm"
                    value="<?= $filtros['data_inicio'] ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1 small">Data Final</label>
                <input type="date" name="data_fim" class="form-control form-control-sm"
                    value="<?= $filtros['data_fim'] ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1 small">Tipo de Movimentação</label>
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <option value="ENTRADA" <?= $filtros['tipo'] == 'ENTRADA' ? 'selected' : '' ?>>Entradas</option>
                    <option value="SAIDA" <?= $filtros['tipo'] == 'SAIDA' ? 'selected' : '' ?>>Saídas</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filtrar Relatório</button>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de Dados (Aparece na tela e na impressão) -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="relatorio-tabela mb-0">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Almoxarifado</th>
                    <th>Responsável</th>
                    <th>Documento / Observação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dados)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Nenhuma movimentação encontrada neste período.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($dados as $d):
                    // Deixa o tipo mais elegante visualmente
                    $tipoTexto = $d['tipo'];
                    $tipoCor = $d['tipo'] == 'ENTRADA' ? '#198754' : '#dc3545';
                    if ($d['subtipo'] == 'TRANSFERENCIA') {
                        $tipoTexto = 'TRANSF.';
                        $tipoCor = '#0dcaf0';
                    }
                    ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($d['data_movimentacao'])) ?></td>
                        <td><strong style="color: <?= $tipoCor ?>;"><?= $tipoTexto ?></strong></td>
                        <td><?= htmlspecialchars($d['almoxarifado']) ?></td>
                        <td><?= htmlspecialchars($d['usuario']) ?></td>
                        <td>
                            <?php
                            // Puxa o documento se existir, senão puxa a observação
                            echo htmlspecialchars($d['documento'] ? 'Doc: ' . $d['documento'] : ($d['observacao'] ?? '—'));
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>