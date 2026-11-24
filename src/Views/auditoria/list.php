<h1 class="mb-4">Logs de Auditoria</h1>
<p class="text-muted">Histórico das últimas 100 alterações no sistema (Quem alterou, o quê e quando).</p>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0" style="font-size: 14px;">
            <thead class="table-dark">
                <tr>
                    <th>Data/Hora</th>
                    <th>Usuário</th>
                    <th>Ação</th>
                    <th>Tabela / ID</th>
                    <th class="text-center">Detalhes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dados)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Nenhum registro de auditoria encontrado.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($dados as $index => $d):
                    // Cores para cada tipo de ação
                    $cores = [
                        'INSERT' => 'bg-success',
                        'UPDATE' => 'bg-warning text-dark',
                        'DELETE' => 'bg-danger'
                    ];
                    $cor = $cores[$d['acao']] ?? 'bg-secondary';
                    ?>
                    <tr>
                        <td><?= date('d/m/Y H:i:s', strtotime($d['criado_em'])) ?></td>
                        <td><strong><?= htmlspecialchars($d['usuario_nome'] ?? 'Sistema/Automático') ?></strong></td>
                        <td><span class="badge <?= $cor ?>"><?= $d['acao'] ?></span></td>
                        <td><?= htmlspecialchars($d['tabela']) ?> <span class="text-muted">(ID:
                                <?= $d['registro_id'] ?>)</span></td>
                        <td class="text-center">
                            <!-- Botão que abre a gaveta (Collapse) -->
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse"
                                data-bs-target="#log-<?= $d['id'] ?>">
                                🔍 Ver Dados
                            </button>
                        </td>
                    </tr>
                    <!-- Linha oculta que aparece quando clica em Ver Dados -->
                    <tr class="collapse" id="log-<?= $d['id'] ?>">
                        <td colspan="5" class="bg-light p-0">
                            <div class="p-3 border-start border-4 border-primary">
                                <pre class="mb-0"
                                    style="font-size: 12px; max-height: 300px; overflow-y: auto; overflow-x: auto; white-space: pre-wrap; word-break: break-all;"><?= json_encode($d['dados'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>