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

        <?php if (in_array($requisicao['status'], ['APROVADA', 'ATENDIDA_PARCIAL']) && $isAprovador): ?>
            <button type="submit" form="formAtendimento" class="btn btn-success" onclick="return confirm('Confirmar a baixa no estoque dos itens preenchidos?')">
                <strong>Confirmar Atendimento</strong>
            </button>
        <?php endif; ?>

        <?php if ($requisicao['status'] === 'AGUARDANDO_APROVACAO' && $isAprovador): ?>
            <a href="?controller=requisicao&action=aprovar&id=<?= $requisicao['id'] ?>" class="btn btn-success" onclick="return confirm('Aprovar esta requisição?')">Aprovar</a>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalRejeitar">Rejeitar</button>
        <?php endif; ?>
        
        <?php 
        // Lógica para exibir o botão de cancelar
            $podeCancelar = false;
            if ($requisicao['status'] !== 'ATENDIDA' && $requisicao['status'] !== 'CANCELADA') {
                if ($isAprovador) {
                    $podeCancelar = true;
                } elseif ($requisicao['solicitante_id'] == $user['id'] && in_array($requisicao['status'], ['RASCUNHO', 'AGUARDANDO_APROVACAO'])) {
                    $podeCancelar = true;
                }
            }
        ?>
        
        <?php if ($podeCancelar): ?>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalCancelar">
                Cancelar Requisição
            </button>
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

<?php if (in_array($requisicao['status'], ['APROVADA', 'ATENDIDA_PARCIAL']) && $isAprovador): ?>
    <!-- Se estiver pronto para atender, abre o formulário -->
        <form id="formAtendimento" action="?controller=requisicao&action=atender" method="POST">
        <input type="hidden" name="id" value="<?= $requisicao['id'] ?>">
<?php endif; ?>

<div class="card shadow-sm">
    <table class="table table-striped mb-0">
        <thead class="table-dark">
            <tr>
                <th>Código</th>
                <th>Produto</th>
                <th>Qtd Aprovada</th>
                <th>Já Atendido</th>
                <th>Pendente</th>
                <?php if (in_array($requisicao['status'], ['APROVADA', 'ATENDIDA_PARCIAL']) && $isAprovador): ?>
                    <th class="bg-success text-white">Entregar Agora</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requisicao['itens'] as $item): 
                // Tratando valores nulos para o PHP 8.2+
                $qtdAprovada = $item['quantidade_aprovada'] !== null ? (float)$item['quantidade_aprovada'] : null;
                $qtdAtendida = $item['quantidade_atendida'] !== null ? (float)$item['quantidade_atendida'] : 0.0;
                $pendente = $qtdAprovada !== null ? ($qtdAprovada - $qtdAtendida) : null;
            ?>
            <tr>
                <td><?= htmlspecialchars($item['codigo']) ?></td>
                <td><?= htmlspecialchars($item['descricao']) ?></td>
                <td>
                    <?= $qtdAprovada !== null ? number_format($qtdAprovada, 3, ',', '.') . ' ' . $item['unidade'] : '—' ?>
                </td>
                <td class="text-success fw-bold">
                    <?= number_format($qtdAtendida, 3, ',', '.') ?>
                </td>
                <td class="text-danger fw-bold">
                    <?= $pendente !== null ? number_format($pendente, 3, ',', '.') : '—' ?>
                </td>
                
                <?php if (in_array($requisicao['status'], ['APROVADA', 'ATENDIDA_PARCIAL']) && $isAprovador): ?>
                    <td>
                        <?php if ($pendente > 0): ?>
                            <input type="number" step="0.001" name="quantidade_atendida[<?= $item['id'] ?>]" 
                                   class="form-control form-control-sm" value="<?= $pendente ?>" max="<?= $pendente ?>" min="0">
                        <?php else: ?>
                            <span class="badge bg-success">Completo</span>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (in_array($requisicao['status'], ['APROVADA', 'ATENDIDA_PARCIAL']) && $isAprovador): ?>
    </form>
<?php endif; ?>

<!-- Modal de Cancelamento -->
<?php if ($podeCancelar): ?>
<div class="modal fade" id="modalCancelar" tabindex="-1">
    <div class="modal-dialog">
        <form action="?controller=requisicao&action=cancelar" method="POST" class="modal-content">
            <input type="hidden" name="id" value="<?= $requisicao['id'] ?>">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Cancelar Requisição</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if ($requisicao['status'] === 'ATENDIDA_PARCIAL'): ?>
                    <div class="alert alert-warning py-2">
                        <small>Atenção: Os itens já entregues serão devolvidos ao estoque automaticamente.</small>
                    </div>
                <?php endif; ?>
                <label class="form-label">Motivo do Cancelamento *</label>
                <textarea name="motivo" class="form-control" required placeholder="Descreva o motivo..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                <button type="submit" class="btn btn-danger">Confirmar Cancelamento</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

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

<?php if ($requisicao['status'] === 'CANCELADA'): ?>
        <div class="alert alert-dark mb-0">
            <strong>Motivo do Cancelamento:</strong> <?= htmlspecialchars($requisicao['motivo_cancelamento'] ?? 'Não informado') ?>
        </div>
<?php endif; ?>