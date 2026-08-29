<?php

class Utilisateur
{
    private PDO $db;

    public function __construct(?PDO $pdo = null)
    {
        $this->db = $pdo ?? Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT id, nom, prenom, email, role FROM utilisateur ORDER BY nom, prenom'
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nom, prenom, email, role FROM utilisateur WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nom, prenom, email, password, role FROM utilisateur WHERE email = :email'
        );
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO utilisateur (nom, prenom, email, password, role)
             VALUES (:nom, :prenom, :email, :password, :role)'
        );
        $stmt->execute([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'client',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'role' => $data['role'],
            'id' => $id,
        ];

        $sql = 'UPDATE utilisateur SET nom = :nom, prenom = :prenom, email = :email, role = :role';

        if (!empty($data['password'])) {
            $sql .= ', password = :password';
            $fields['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql .= ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($fields);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM utilisateur WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        if ($user === null || !password_verify($password, $user['password'])) {
            return null;
        }
        unset($user['password']);
        return $user;
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT id FROM utilisateur WHERE email = :email';
        $params = ['email' => $email];
        if ($exceptId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $exceptId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    public function countByRole(): array
    {
        $stmt = $this->db->query(
            'SELECT role, COUNT(*) AS total FROM utilisateur GROUP BY role'
        );
        return $stmt->fetchAll();
    }
}
