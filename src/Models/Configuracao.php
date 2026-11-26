<?php
namespace App\Models;

use App\Core\Database;

class Configuracao
{
    public function get(): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM configuracoes WHERE id = 1");
        return $stmt->fetch() ?: null;
    }

    public function update(array $data): bool
    {
        $sql = "UPDATE configuracoes 
                   SET razao_social = :razao, nome_fantasia = :fantasia, cnpj = :cnpj, cor_primaria = :cor";
        
        // Se uma nova logo foi enviada, adiciona no SQL
        if (!empty($data['logo_path'])) {
            $sql .= ", logo_path = :logo";
        }
        
        $sql .= " WHERE id = 1";
        
        $stmt = Database::getConnection()->prepare($sql);
        
        $params = [
            'razao' => $data['razao_social'],
            'fantasia' => $data['nome_fantasia'],
            'cnpj' => $data['cnpj'],
            'cor' => $data['cor_primaria'] ?? '#0088B7'
        ];
        
        if (!empty($data['logo_path'])) {
            $params['logo'] = $data['logo_path'];
        }

        return $stmt->execute($params);
    }
}