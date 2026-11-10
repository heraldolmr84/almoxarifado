<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Produto
{
    // Modificado para trazer apenas produtos ativos
    public function all(): array
    {
        $stmt = Database::getConnection()->query("
            SELECT p.*, c.nome AS categoria, u.sigla AS unidade
              FROM produtos p
              LEFT JOIN categorias c ON c.id = p.categoria_id
              JOIN unidades_medida u ON u.id = p.unidade_medida_id
             WHERE p.ativo = TRUE
             ORDER BY p.descricao
        ");
        return $stmt->fetchAll();
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
}