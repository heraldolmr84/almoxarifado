<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Fornecedores</h1>
    <a href="?controller=fornecedor&action=create" class="btn btn-primary">Novo Fornecedor</a>
</div>

<?php if (isset($mensagem) && $mensagem): ?>
    <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show">
        <?= htmlspecialchars($mensagem['texto']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <table class="table table-striped mb-0">
        <thead class="table-dark">
            <tr>
                <th>Razão Social</th>
                <th>Nome Fantasia</th>
                <th>CNPJ</th>
                <th>Contato</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fornecedores as $f): ?>
                <tr>
                    <td><?= htmlspecialchars($f['razao_social']) ?></td>
                    <td><?= htmlspecialchars($f['nome_fantasia'] ?? '—') ?></td>
                    <td>
                        <?php
                        // Garante que não é nulo antes de usar o strlen
                        $cnpjFormatado = $f['cnpj'] ?? '';

                        if (strlen($cnpjFormatado) == 14) {
                            $cnpjFormatado = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpjFormatado);
                        } elseif (strlen($cnpjFormatado) == 11) {
                            $cnpjFormatado = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cnpjFormatado);
                        }

                        echo $cnpjFormatado ? htmlspecialchars($cnpjFormatado) : '—';
                        ?>
                    </td>
                    <td>
                        <?php
                        // Garante que não é nulo antes de usar o strlen
                        $tel = $f['telefone'] ?? '';

                        if (strlen($tel) == 11) {
                            $tel = preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $tel);
                        } elseif (strlen($tel) == 10) {
                            $tel = preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $tel);
                        }

                        echo $tel ? htmlspecialchars($tel) : '—';
                        ?>
                        <br>
                        <small class="text-muted">
                            <?= htmlspecialchars($f['email'] ?? '') ?>
                        </small>
                    </td>
                    <td class="text-end">
                        <a href="?controller=fornecedor&action=edit&id=<?= $f['id'] ?>"
                            class="btn btn-sm btn-primary">Editar</a>
                        <a href="?controller=fornecedor&action=delete&id=<?= $f['id'] ?>" class="btn btn-sm btn-danger"
                            onclick="return confirm('Desativar?');">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>