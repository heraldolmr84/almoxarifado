<?php 
$isEdit = isset($usuario); 
$action = $isEdit ? '?controller=usuario&action=update' : '?controller=usuario&action=store';
?>
<h1><?= $isEdit ? 'Editar Usuário' : 'Novo Usuário' ?></h1>
<div class="card shadow-sm mt-4">
    <div class="card-body">
        <form action="<?= $action ?>" method="POST">
            <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $usuario['id'] ?>"><?php endif; ?>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Nome *</label>
                    <input type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">E-mail *</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($usuario['email'] ?? '') ?>">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label">Perfil de Acesso *</label>
                    <select name="perfil" class="form-select" required>
                        <option value="OPERADOR" <?= (isset($usuario['perfil']) && $usuario['perfil'] == 'OPERADOR') ? 'selected' : '' ?>>Operador (Solicitante)</option>
                        <option value="ALMOXARIFE" <?= (isset($usuario['perfil']) && $usuario['perfil'] == 'ALMOXARIFE') ? 'selected' : '' ?>>Almoxarife (Aprovador)</option>
                        <option value="ADMIN" <?= (isset($usuario['perfil']) && $usuario['perfil'] == 'ADMIN') ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Senha <?= $isEdit ? '(deixe em branco para não alterar)' : '*' ?></label>
                    <input type="password" name="senha" class="form-control" <?= !$isEdit ? 'required' : '' ?>>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="?controller=usuario&action=list" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>