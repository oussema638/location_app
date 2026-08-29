<?php

class Equipement
{
    private PDO $db;

    public function __construct(?PDO $pdo = null)
    {
        $this->db = $pdo ?? Database::getConnection();
    }

    public function findAll(?int $categorieId = null, ?string $etat = null): array
    {
        $sql = 'SELECT e.*, c.nom AS categorie_nom
                FROM equipement e
                INNER JOIN categorie c ON c.id = e.categorie_id
                WHERE 1 = 1';
        $params = [];

        if ($categorieId !== null) {
            $sql .= ' AND e.categorie_id = :categorie_id';
            $params['categorie_id'] = $categorieId;
        }
        if ($etat !== null && $etat !== '') {
            $sql .= ' AND e.etat = :etat';
            $params['etat'] = $etat;
        }

        $sql .= ' ORDER BY e.nom';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findDisponibles(): array
    {
        return $this->findAll(null, 'disponible');
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*, c.nom AS categorie_nom, c.description AS categorie_description
             FROM equipement e
             INNER JOIN categorie c ON c.id = e.categorie_id
             WHERE e.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByCategorie(int $categorieId): array
    {
        return $this->findAll($categorieId);
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO equipement
                (nom, description, prix_jour, quantite_stock, seuil_alerte, etat, categorie_id)
             VALUES
                (:nom, :description, :prix_jour, :quantite_stock, :seuil_alerte, :etat, :categorie_id)'
        );
        $stmt->execute($this->mapData($data));
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE equipement SET
                nom = :nom,
                description = :description,
                prix_jour = :prix_jour,
                quantite_stock = :quantite_stock,
                seuil_alerte = :seuil_alerte,
                etat = :etat,
                categorie_id = :categorie_id
             WHERE id = :id'
        );
        $params = $this->mapData($data);
        $params['id'] = $id;
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM equipement WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function updateEtat(int $id, string $etat): bool
    {
        $stmt = $this->db->prepare('UPDATE equipement SET etat = :etat WHERE id = :id');
        return $stmt->execute(['etat' => $etat, 'id' => $id]);
    }

    public function decrementStock(int $id, int $qty = 1): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE equipement
             SET quantite_stock = GREATEST(quantite_stock - :qty, 0)
             WHERE id = :id AND quantite_stock >= :qty_check'
        );
        return $stmt->execute([
            'qty' => $qty,
            'qty_check' => $qty,
            'id' => $id,
        ]);
    }

    public function incrementStock(int $id, int $qty = 1): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE equipement SET quantite_stock = quantite_stock + :qty WHERE id = :id'
        );
        return $stmt->execute(['qty' => $qty, 'id' => $id]);
    }

    public function findLowStock(): array
    {
        $stmt = $this->db->query(
            'SELECT e.*, c.nom AS categorie_nom
             FROM equipement e
             INNER JOIN categorie c ON c.id = e.categorie_id
             WHERE e.quantite_stock <= e.seuil_alerte
             ORDER BY e.quantite_stock ASC, e.nom'
        );
        return $stmt->fetchAll();
    }

    public function countByEtat(): array
    {
        $stmt = $this->db->query(
            'SELECT etat, COUNT(*) AS total FROM equipement GROUP BY etat'
        );
        return $stmt->fetchAll();
    }

    private function mapData(array $data): array
    {
        return [
            'nom' => $data['nom'],
            'description' => $data['description'] ?? null,
            'prix_jour' => $data['prix_jour'],
            'quantite_stock' => (int) $data['quantite_stock'],
            'seuil_alerte' => (int) ($data['seuil_alerte'] ?? 1),
            'etat' => $data['etat'] ?? 'disponible',
            'categorie_id' => (int) $data['categorie_id'],
        ];
    }
}
