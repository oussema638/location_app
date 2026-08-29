<?php

class Categorie
{
    private PDO $db;

    public function __construct(?PDO $pdo = null)
    {
        $this->db = $pdo ?? Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT c.*, COUNT(e.id) AS nb_equipements
             FROM categorie c
             LEFT JOIN equipement e ON e.categorie_id = c.id
             GROUP BY c.id
             ORDER BY c.nom'
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categorie WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO categorie (nom, description) VALUES (:nom, :description)'
        );
        $stmt->execute([
            'nom' => $data['nom'],
            'description' => $data['description'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE categorie SET nom = :nom, description = :description WHERE id = :id'
        );
        return $stmt->execute([
            'nom' => $data['nom'],
            'description' => $data['description'] ?? null,
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM categorie WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
