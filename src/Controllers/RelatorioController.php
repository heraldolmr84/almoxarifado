<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Auth;

class RelatorioController
{
    public function __construct()
    {
        // Apenas Admin, Almoxarife e Gerente podem ver relatórios
        if (!in_array(Auth::user()['perfil'], ['ADMIN', 'ALMOXARIFE', 'GERENTE'])) {
            die("Acesso negado.");
        }
    }

    // Tela inicial com os botões
    public function indexAction(): void
    {
        $this->render('relatorios/index');
    }

    // Relatório 1: Extrato Geral de Movimentações
    public function movimentacoesAction(): void
    {
        $filtros = [
            'data_inicio' => $_GET['data_inicio'] ?? date('Y-m-01'), // Padrão: 1º dia do mês
            'data_fim' => $_GET['data_fim'] ?? date('Y-m-t'),        // Padrão: último dia do mês
            'tipo' => $_GET['tipo'] ?? ''
        ];

        $db = Database::getConnection();
        $sql = "SELECT m.*, u.nome AS usuario, 
                       COALESCE(ad.nome, ao.nome) AS almoxarifado
                  FROM movimentacoes m
                  JOIN usuarios u ON u.id = m.usuario_id
                  LEFT JOIN almoxarifados ad ON ad.id = m.almoxarifado_destino_id
                  LEFT JOIN almoxarifados ao ON ao.id = m.almoxarifado_origem_id
                 WHERE m.data_movimentacao >= :inicio AND m.data_movimentacao <= :fim";

        $params = [
            'inicio' => $filtros['data_inicio'] . ' 00:00:00',
            'fim' => $filtros['data_fim'] . ' 23:59:59'
        ];

        if (!empty($filtros['tipo'])) {
            $sql .= " AND m.tipo = :tipo";
            $params['tipo'] = $filtros['tipo'];
        }

        $sql .= " ORDER BY m.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $this->render('relatorios/movimentacoes', [
            'dados' => $stmt->fetchAll(),
            'filtros' => $filtros
        ]);
    }

    // Relatório 2: Consumo por Departamento
    public function consumoAction(): void
    {
        $filtros = [
            'data_inicio' => $_GET['data_inicio'] ?? date('Y-m-01'),
            'data_fim' => $_GET['data_fim'] ?? date('Y-m-t')
        ];

        // Esta query soma a quantidade que saiu para cada departamento através de Requisições
        $sql = "SELECT d.nome AS departamento, 
                       COUNT(DISTINCT r.id) AS qtd_requisicoes,
                       SUM(ri.quantidade_atendida) AS total_itens
                  FROM requisicoes_itens ri
                  JOIN requisicoes r ON r.id = ri.requisicao_id
                  JOIN departamentos d ON d.id = r.departamento_id
                 WHERE r.status IN ('ATENDIDA', 'ATENDIDA_PARCIAL')
                   AND r.criado_em >= :inicio AND r.criado_em <= :fim
                 GROUP BY d.id, d.nome
                 ORDER BY total_itens DESC";

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute([
            'inicio' => $filtros['data_inicio'] . ' 00:00:00',
            'fim' => $filtros['data_fim'] . ' 23:59:59'
        ]);

        $this->render('relatorios/consumo', [
            'dados' => $stmt->fetchAll(),
            'filtros' => $filtros
        ]);
    }

    // Relatório 3: Produtos sem Movimentação (Estoque Parado)
    public function semMovimentacaoAction(): void
    {
        $filtros = [
            'data_limite' => $_GET['data_limite'] ?? date('Y-m-d', strtotime('-30 days')) // Padrão 30 dias
        ];

        $sql = "SELECT p.codigo, p.descricao, COALESCE(SUM(s.quantidade), 0) AS estoque_atual
                  FROM produtos p
                  LEFT JOIN saldo_estoque s ON s.produto_id = p.id
                 WHERE p.ativo = TRUE
                   AND p.id NOT IN (
                       SELECT i.produto_id 
                         FROM itens_movimentacao i
                         JOIN movimentacoes m ON m.id = i.movimentacao_id
                        WHERE m.data_movimentacao >= :data_limite
                   )
                 GROUP BY p.id, p.codigo, p.descricao
                HAVING COALESCE(SUM(s.quantidade), 0) > 0
                 ORDER BY p.descricao";

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute(['data_limite' => $filtros['data_limite'] . ' 00:00:00']);

        $this->render('relatorios/sem_movimentacao', [
            'dados' => $stmt->fetchAll(),
            'filtros' => $filtros
        ]);
    }

    // Relatório: Compras por Período
    // Relatório: Compras por Período (Agora lendo de Movimentações)
    public function comprasAction(): void
    {
        $filtros = [
            'data_inicio' => $_GET['data_inicio'] ?? date('Y-m-01'),
            'data_fim' => $_GET['data_fim'] ?? date('Y-m-t')
        ];

        $db = Database::getConnection();

        // Lê as MOVIMENTAÇÕES de ENTRADA (seja de pedido de compra ou entrada direta)
        $sql = "SELECT m.id, COALESCE(m.documento, 'S/N') AS documento, 
                       m.data_movimentacao AS data_compra, m.subtipo, 
                       f.razao_social AS fornecedor,
                       u.nome AS usuario,
                       COALESCE(SUM(i.quantidade * i.valor_unitario), 0) AS total_pedido
                  FROM movimentacoes m
                  JOIN itens_movimentacao i ON i.movimentacao_id = m.id
                  JOIN usuarios u ON u.id = m.usuario_id
                  LEFT JOIN fornecedores f ON f.id = m.fornecedor_id
                 WHERE m.tipo = 'ENTRADA' AND m.status = 'CONFIRMADA'
                   AND m.data_movimentacao >= :inicio AND m.data_movimentacao <= :fim
                   AND i.valor_unitario IS NOT NULL AND i.valor_unitario > 0
                 GROUP BY m.id, f.razao_social, u.nome
                 ORDER BY m.data_movimentacao DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'inicio' => $filtros['data_inicio'] . ' 00:00:00',
            'fim' => $filtros['data_fim'] . ' 23:59:59'
        ]);

        $this->render('relatorios/compras', [
            'dados' => $stmt->fetchAll(),
            'filtros' => $filtros
        ]);
    }

    // Relatório: Materiais Comprados por Período
    // Relatório: Materiais Comprados por Período (Agora lendo de Movimentações)
    public function materiaisCompradosAction(): void
    {
        $filtros = [
            'data_inicio' => $_GET['data_inicio'] ?? date('Y-m-01'),
            'data_fim' => $_GET['data_fim'] ?? date('Y-m-t'),
            'produto_id' => $_GET['produto_id'] ?? ''
        ];

        $db = Database::getConnection();

        $sql = "SELECT COALESCE(m.documento, 'S/N') AS pedido_numero, 
                       m.data_movimentacao AS data_compra, 
                       m.subtipo, f.razao_social AS fornecedor,
                       pr.codigo AS produto_codigo, pr.descricao AS produto_descricao, u.sigla AS unidade,
                       i.quantidade, i.valor_unitario, (i.quantidade * i.valor_unitario) AS subtotal
                  FROM movimentacoes m
                  JOIN itens_movimentacao i ON i.movimentacao_id = m.id
                  JOIN produtos pr ON pr.id = i.produto_id
                  JOIN unidades_medida u ON u.id = pr.unidade_medida_id
                  LEFT JOIN fornecedores f ON f.id = m.fornecedor_id
                 WHERE m.tipo = 'ENTRADA' AND m.status = 'CONFIRMADA'
                   AND m.data_movimentacao >= :inicio AND m.data_movimentacao <= :fim
                   AND i.valor_unitario IS NOT NULL AND i.valor_unitario > 0";

        $params = [
            'inicio' => $filtros['data_inicio'] . ' 00:00:00',
            'fim' => $filtros['data_fim'] . ' 23:59:59'
        ];

        if (!empty($filtros['produto_id'])) {
            $sql .= " AND i.produto_id = :produto_id";
            $params['produto_id'] = $filtros['produto_id'];
        }

        $sql .= " ORDER BY m.data_movimentacao DESC, pr.descricao";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $produtos = $db->query("SELECT id, codigo, descricao FROM produtos WHERE ativo = TRUE ORDER BY descricao")->fetchAll();

        $this->render('relatorios/materiais_comprados', [
            'dados' => $stmt->fetchAll(),
            'filtros' => $filtros,
            'produtos' => $produtos
        ]);
    }

    private function render(string $view, array $data = []): void
    {

        $data['config'] = (new \App\Models\Configuracao())->get();

        extract($data);
        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . "/../Views/{$view}.php";
        require __DIR__ . '/../Views/layout/footer.php';
    }
}