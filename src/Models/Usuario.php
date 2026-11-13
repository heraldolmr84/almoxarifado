<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Usuario
{
    public function findByEmail(string $email): ?array
    {
        $stmt = Database::getConnection()->prepare("SELECT * FROM usuarios WHERE email = :email AND ativo = TRUE");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function all(): array
    {
        return Database::getConnection()->query("SELECT id, nome, email, perfil, ativo FROM usuarios WHERE ativo = TRUE ORDER BY nome")->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO usuarios (nome, email, senha_hash, perfil, departamento_id, deve_trocar_senha, ativo) 
                VALUES (:nome, :email, :senha, :perfil, :departamento_id, :trocar, TRUE)";
        $stmt = Database::getConnection()->prepare($sql);

        return $stmt->execute([
            'nome' => $data['nome'],
            'email' => $data['email'],
            'senha' => password_hash($data['senha'], PASSWORD_DEFAULT),
            'perfil' => $data['perfil'],
            'departamento_id' => !empty($data['departamento_id']) ? $data['departamento_id'] : null,
            'trocar' => isset($data['deve_trocar_senha']) // Se o checkbox foi marcado, vai true
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE usuarios SET nome = :nome, email = :email, perfil = :perfil, departamento_id = :dep_id, deve_trocar_senha = :trocar";

        // Se o campo de senha foi preenchido, adiciona no SQL
        if (!empty($data['senha'])) {
            $sql .= ", senha_hash = :senha";
        }

        $sql .= " WHERE id = :id";

        $stmt = Database::getConnection()->prepare($sql);

        // Bind dos dados básicos
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':perfil', $data['perfil']);

        // Bind do Departamento (Se estiver vazio, envia NULL de verdade)
        if (!empty($data['departamento_id'])) {
            $stmt->bindValue(':dep_id', (int) $data['departamento_id'], PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':dep_id', null, PDO::PARAM_NULL);
        }

        // Bind do Checkbox forçando como Booleano para o Postgres
        $stmt->bindValue(':trocar', isset($data['deve_trocar_senha']), PDO::PARAM_BOOL);

        // Bind da Senha, se houver
        if (!empty($data['senha'])) {
            $stmt->bindValue(':senha', password_hash($data['senha'], PASSWORD_DEFAULT));
        }

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Método para trocar a própria senha do usuário logado
    public function trocarPropriaSenha(int $id, string $novaSenha): bool
    {
        $sql = "UPDATE usuarios SET senha_hash = :senha, deve_trocar_senha = FALSE WHERE id = :id";
        $stmt = Database::getConnection()->prepare($sql);
        return $stmt->execute([
            'senha' => password_hash($novaSenha, PASSWORD_DEFAULT),
            'id' => $id
        ]);
    }

    public function delete(int $id): bool
    {
        // Soft delete para manter o histórico de quem fez o quê
        $stmt = Database::getConnection()->prepare("UPDATE usuarios SET ativo = FALSE WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    // Gera o token e salva no banco
    public function gerarTokenRecuperacao(string $email): ?string
    {
        $db = Database::getConnection();
        $usuario = $this->findByEmail($email);

        if (!$usuario) {
            return null; // E-mail não encontrado
        }

        // Gera um token aleatório seguro (64 caracteres)
        $token = bin2hex(random_bytes(32));

        // O ideal é salvar o HASH do token no banco por segurança
        $tokenHash = hash('sha256', $token);

        // Define a validade para 1 hora a partir de agora
        $expiraEm = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $sql = "UPDATE usuarios SET reset_token = :token, reset_expira_em = :expira WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['token' => $tokenHash, 'expira' => $expiraEm, 'id' => $usuario['id']]);

        // Retorna o token limpo (sem hash) para enviar no link do e-mail
        return $token;
    }

    // Valida se o token existe e não expirou
    public function validarToken(string $token): ?array
    {
        $tokenHash = hash('sha256', $token);

        $sql = "SELECT * FROM usuarios 
                 WHERE reset_token = :token 
                   AND reset_expira_em > NOW() 
                   AND ativo = TRUE";
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute(['token' => $tokenHash]);
        return $stmt->fetch() ?: null;
    }

    // Altera a senha e limpa o token
    public function atualizarSenhaComToken(int $id, string $novaSenha): bool
    {
        $sql = "UPDATE usuarios 
                   SET senha_hash = :senha, reset_token = NULL, reset_expira_em = NULL 
                 WHERE id = :id";
        $stmt = Database::getConnection()->prepare($sql);
        return $stmt->execute([
            'senha' => password_hash($novaSenha, PASSWORD_DEFAULT),
            'id' => $id
        ]);
    }
}