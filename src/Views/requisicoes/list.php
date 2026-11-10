<?php if (isset($mensagem) && $mensagem): ?>
    <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show">
        <?= htmlspecialchars($mensagem['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Requisições</h1>
    <a href="?controller=requisicao&action=create" class="btn btn-primary">Nova Requisição</a>
</div>

<div class="card shadow-sm">
    <table class="table table-striped mb-0">
        <thead class="table-dark">
            <tr>
                <th>Número</th>
                <th>Data</th>
                <th>Departamento</th>
                <th>Solicitante</th>
                <th>Prioridade</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($requisicoes)): ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">Nenhuma requisição encontrada.</td></tr>
            <?php endif; ?>
            
            <?php foreach ($requisicoes as $r): 
                $cores = [
                    'RASCUNHO' => 'bg-secondary',
                    'AGUARDANDO_APROVACAO' => 'bg-warning text-dark',
                    'APROVADA' => 'bg-info text-dark',
                    'REJEITADA' => 'bg-danger',
                    'ATENDIDA' => 'bg-success'
                ];
                $cor = $cores[$r['status']] ?? 'bg-secondary';
            ?>
            <tr>
                <td><a href="?controller=requisicao&action=show&id=<?= $r['id'] ?>"><strong><?= $r['numero'] ?></strong></a></td>
                <td><?= date('d/m/Y H:i', strtotime($r['criado_em'])) ?></td>
                <td><?= htmlspecialchars($r['departamento']) ?></td>
                <td><?= htmlspecialchars($r['solicitante']) ?></td>
                <td><?= $r['prioridade'] ?></td>
                <td><span class="badge <?= $cor ?>"><?= $r['status'] ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>