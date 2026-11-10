<?php 
use App\Core\Auth;
$user = Auth::user(); 
$isAprovador = in_array($user['perfil'], ['ADMIN', 'ALMOXARIFE']);
?>

<?php if (isset($mensagem) && $mensagem): ?>
    <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show">
        <?= htmlspecialchars($mensagem['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Requisição <?= $requisicao['numero'] ?></h2>
    <div>
        <?php if ($requisicao['status'] === 'RASCUNHO' && $requisicao['solicitante_id'] == $user['id']): ?>
            <a href="?controller=requisicao&action=enviar&id=<?= $requisicao['id'] ?>" class="btn btn-warning">Enviar para Aprovação</a>
        <?php endif; ?>

        <?php if ($requisicao['status'] === 'AGUARDANDO_APROVACAO' && $isAprovador): ?>
            <a href="?controller=requisicao&action=aprovar&id=<?= $requisicao['id'] ?>" class="btn btn-success" onclick="return confirm('Aprovar esta requisição?')">Aprovar</a>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalRejeitar">Rejeitar</button>
        <?php endif; ?>
        
        <a href="?controller=requisicao&action=list" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><strong>Status:</strong> <br> <span class="badge bg-primary fs-6"><?= $requisicao['status'] ?></span></div>
            <div class="col-md-3"><strong>Solicitante:</strong> <br> <?= htmlspecialchars($requisicao['solicitante']) ?></div>
            <div class="col-md-3"><strong>Departamento:</strong> <br> <?= htmlspecialchars($requisicao['departamento']) ?></div>
            <div class="col-md-3"><strong>Prioridade:</strong> <br> <?= $requisicao['prioridade'] ?></div>
        </div>
        <hr>
        <p><strong>Justificativa:</strong> <?= nl2br(htmlspecialchars($requisicao['justificativa'])) ?></p>
        
        <?php if ($requisicao['status'] === 'REJEITADA'): ?>
            <div class="alert alert-danger mb-0">
                <strong>Motivo da Rejeição:</strong> <?= htmlspecialchars($requisicao['motivo_rejeicao']) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<h4>Itens Solicitados</h4>
<div class="card shadow-sm">
    <table class="table table-striped mb-0">
        <thead class="table-dark">
            <tr>
                <th>Código</th>
                <th>Produto</th>
                <th>Qtd Solicitada</th>
                <th>Qtd Aprovada</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requisicao['itens'] as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['codigo']) ?></td>
                <td><?= htmlspecialchars($item['descricao']) ?></td>
                <td><?= $item['quantidade_solicitada'] ?> <?= $item['unidade'] ?></td>
                <td><?= $item['quantidade_aprovada'] ? $item['quantidade_aprovada'] . ' ' . $item['unidade'] : '—' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal de Rejeição -->
<?php if ($requisicao['status'] === 'AGUARDANDO_APROVACAO' && $isAprovador): ?>
<div class="modal fade" id="modalRejeitar" tabindex="-1">
    <div class="modal-dialog">
        <form action="?controller=requisicao&action=rejeitar" method="POST" class="modal-content">
            <input type="hidden" name="id" value="<?= $requisicao['id'] ?>">
            <div class="modal-header">
                <h5 class="modal-title">Rejeitar Requisição</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Motivo da Rejeição *</label>
                <textarea name="motivo" class="form-control" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger">Confirmar Rejeição</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>