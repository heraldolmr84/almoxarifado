<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Departamento
{
    public function all(): array
    {
        $sql = "SELECT d.*, COUNT(da.almoxarifado_id) AS total_almox 
                  FROM departamentos d
                  LEFT JOIN departamentos_almoxarifados da ON da.departamento_id = d.id
                 WHERE d.ativo = TRUE
                 GROUP BY d.id
                 ORDER BY d.nome";
        return Database::getConnection()->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT * FROM departamentos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $depto = $stmt->fetch();
        
        if (!$depto) return null;

        // Busca os IDs dos almoxarifados vinculados a este departamento
        $stmtAlmox = $db->prepare("SELECT almoxarifado_id FROM departamentos_almoxarifados WHERE departamento_id = :id");
        $stmtAlmox->execute(['id' => $id]);
        $depto['almoxarifados'] = $stmtAlmox->fetchAll(PDO::FETCH_COLUMN); // Retorna um array simples: [1, 2, 3]

        return $depto;
    }

    public function getAlmoxarifados(): array
    {
        return Database::getConnection()->query("SELECT id, nome FROM almoxarifados WHERE ativo = TRUE ORDER BY nome")->fetchAll();
    }

    public function create(array $data): bool
    {
        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            // 1. Insere o departamento
            $stmt = $db->prepare("INSERT INTO departamentos (nome) VALUES (:nome) RETURNING id");
            $stmt->execute(['nome' => $data['nome']]);
            $deptoId = $stmt->fetchColumn();

            // 2. Insere os vínculos (se houver algum selecionado)
            if (!empty($data['almoxarifados'])) {
                $stmtVinculo = $db->prepare("INSERT INTO departamentos_almoxarifados (departamento_id, almoxarifado_id) VALUES (:dep, :almox)");
                foreach ($data['almoxarifados'] as $almoxId) {
                    $stmtVinculo->execute(['dep' => $deptoId, 'almox' => $almoxId]);
                }
            }

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            // 1. Atualiza o nome
            $db->prepare("UPDATE departamentos SET nome = :nome WHERE id = :id")
               ->execute(['nome' => $data['nome'], 'id' => $id]);

            // 2. Apaga os vínculos antigos
            $db->prepare("DELETE FROM departamentos_almoxarifados WHERE departamento_id = :id")
               ->execute(['id' => $id]);

            // 3. Insere os novos vínculos
            if (!empty($data['almoxarifados'])) {
                $stmtVinculo = $db->prepare("INSERT INTO departamentos_almoxarifados (departamento_id, almoxarifado_id) VALUES (:dep, :almox)");
                foreach ($data['almoxarifados'] as $almoxId) {
                    $stmtVinculo->execute(['dep' => $id, 'almox' => $almoxId]);
                }
            }

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        // Soft delete para não quebrar históricos de requisições
        $stmt = Database::getConnection()->prepare("UPDATE departamentos SET ativo = FALSE WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}