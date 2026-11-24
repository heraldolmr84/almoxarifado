<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Produto
{
    public function all(array $filtros = [], int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT p.*, c.nome AS categoria, u.sigla AS unidade
                  FROM produtos p
                  LEFT JOIN categorias c ON c.id = p.categoria_id
                  JOIN unidades_medida u ON u.id = p.unidade_medida_id
                 WHERE p.ativo = TRUE";

        $params = [];

        if (!empty($filtros['codigo'])) {
            $sql .= " AND p.codigo ILIKE :codigo";
            $params['codigo'] = '%' . $filtros['codigo'] . '%';
        }
        if (!empty($filtros['descricao'])) {
            $sql .= " AND p.descricao ILIKE :descricao";
            $params['descricao'] = '%' . $filtros['descricao'] . '%';
        }
        if (!empty($filtros['categoria_id'])) {
            $sql .= " AND p.categoria_id = :categoria_id";
            $params['categoria_id'] = $filtros['categoria_id'];
        }

        // Adicionando a paginação na query
        $sql .= " ORDER BY p.descricao LIMIT :limit OFFSET :offset";

        $stmt = Database::getConnection()->prepare($sql);

        // Bind dos filtros normais
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        // O PostgreSQL exige que LIMIT e OFFSET sejam tratados como INT
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(array $filtros = []): int
    {
        $sql = "SELECT COUNT(*) FROM produtos p WHERE p.ativo = TRUE";
        $params = [];

        if (!empty($filtros['codigo'])) {
            $sql .= " AND p.codigo ILIKE :codigo";
            $params['codigo'] = '%' . $filtros['codigo'] . '%';
        }
        if (!empty($filtros['descricao'])) {
            $sql .= " AND p.descricao ILIKE :descricao";
            $params['descricao'] = '%' . $filtros['descricao'] . '%';
        }
        if (!empty($filtros['categoria_id'])) {
            $sql .= " AND p.categoria_id = :categoria_id";
            $params['categoria_id'] = $filtros['categoria_id'];
        }

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getCategorias(): array
    {
        $stmt = Database::getConnection()->query("SELECT id, nome FROM categorias WHERE ativo = TRUE ORDER BY nome");
        return $stmt->fetchAll();
    }

    public function getUnidades(): array
    {
        $stmt = Database::getConnection()->query("SELECT id, sigla FROM unidades_medida ORDER BY sigla");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare("SELECT * FROM produtos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $produto = $stmt->fetch();
        return $produto ?: null;
    }

    public function getProdutosParaCompra(): array
    {
        // Traz todos os produtos ativos, não importa se tem estoque ou não
        $sql = "SELECT id, codigo, descricao 
                  FROM produtos 
                 WHERE ativo = TRUE 
                 ORDER BY descricao";

        return Database::getConnection()->query($sql)->fetchAll();
    }

    public function create(array $data): bool
    {

        
        $sql = "INSERT INTO produtos (codigo, descricao, categoria_id, unidade_medida_id, estoque_minimo, controla_lote)
                VALUES (:codigo, :descricao, :categoria_id, :unidade_medida_id, :estoque_minimo, :controla_lote)";

        $stmt = Database::getConnection()->prepare($sql);

        // Vinculando os parâmetros explicitamente
        $stmt->bindValue(':codigo', $data['codigo']);
        $stmt->bindValue(':descricao', $data['descricao']);

        if (!empty($data['categoria_id'])) {
            $stmt->bindValue(':categoria_id', $data['categoria_id'], PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':categoria_id', null, PDO::PARAM_NULL);
        }

        $stmt->bindValue(':unidade_medida_id', $data['unidade_medida_id'], PDO::PARAM_INT);
        $stmt->bindValue(':estoque_minimo', !empty($data['estoque_minimo']) ? $data['estoque_minimo'] : 0);

        // AQUI ESTÁ A CORREÇÃO: Forçando o envio como Booleano nativo para o Postgres
        $stmt->bindValue(':controla_lote', isset($data['controla_lote']), PDO::PARAM_BOOL);

        return $stmt->execute();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE produtos 
                   SET codigo = :codigo, 
                       descricao = :descricao, 
                       categoria_id = :categoria_id, 
                       unidade_medida_id = :unidade_medida_id, 
                       estoque_minimo = :estoque_minimo, 
                       controla_lote = :controla_lote
                 WHERE id = :id";

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':codigo', $data['codigo']);
        $stmt->bindValue(':descricao', $data['descricao']);

        if (!empty($data['categoria_id'])) {
            $stmt->bindValue(':categoria_id', $data['categoria_id'], PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':categoria_id', null, PDO::PARAM_NULL);
        }

        $stmt->bindValue(':unidade_medida_id', $data['unidade_medida_id'], PDO::PARAM_INT);
        $stmt->bindValue(':estoque_minimo', !empty($data['estoque_minimo']) ? $data['estoque_minimo'] : 0);
        $stmt->bindValue(':controla_lote', isset($data['controla_lote']), PDO::PARAM_BOOL);

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = Database::getConnection()->prepare("UPDATE produtos SET ativo = FALSE WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function gerarProximoSKU(int $categoriaId): string
    {
        $db = Database::getConnection();

        // 1. Busca o prefixo da categoria
        $stmt = $db->prepare("SELECT prefixo_sku FROM categorias WHERE id = :id");
        $stmt->execute(['id' => $categoriaId]);
        $prefixo = $stmt->fetchColumn() ?: 'GEN'; // GEN = Genérico, se não tiver prefixo

        $anoAtual = date('y'); // Retorna '26' para 2026
        $baseSKU = "{$prefixo}-{$anoAtual}-";

        // 2. Busca o último SKU cadastrado para esta categoria neste ano
        // Ex: Busca por 'INF-26-%' e ordena decrescente para pegar o maior
        $stmt = $db->prepare("SELECT codigo FROM produtos WHERE codigo LIKE :base ORDER BY codigo DESC LIMIT 1");
        $stmt->execute(['base' => $baseSKU . '%']);
        $ultimoSKU = $stmt->fetchColumn();

        if ($ultimoSKU) {
            // Pega os 3 últimos dígitos e soma 1 (Ex: de '001' vira 002)
            $numeroSequencial = (int) substr($ultimoSKU, -3) + 1;
        } else {
            $numeroSequencial = 1;
        }

        // 3. Formata o número com 3 dígitos (001, 002, etc)
        $sequencialFormatado = str_pad($numeroSequencial, 3, '0', STR_PAD_LEFT);

        return $baseSKU . $sequencialFormatado;
    }
}
