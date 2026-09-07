<?php

class Location
{
    private PDO $db;

    public function __construct(?PDO $pdo = null)
    {
        $this->db = $pdo ?? Database::getConnection();
    }

    public function findAll(?string $statut = null): array
    {
        $sql = $this->baseSelect() . ' WHERE 1 = 1';
        $params = [];

        if ($statut !== null && $statut !== '') {
            $sql .= ' AND l.statut = :statut';
            $params['statut'] = $statut;
        }

        $sql .= ' ORDER BY l.date_debut DESC, l.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE l.id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByUtilisateur(int $utilisateurId): array
    {
        $stmt = $this->db->prepare(
            $this->baseSelect() . ' WHERE l.utilisateur_id = :utilisateur_id ORDER BY l.date_debut DESC'
        );
        $stmt->execute(['utilisateur_id' => $utilisateurId]);
        return $stmt->fetchAll();
    }

    public function findByEquipement(int $equipementId): array
    {
        $stmt = $this->db->prepare(
            $this->baseSelect() . ' WHERE l.equipement_id = :equipement_id ORDER BY l.date_debut DESC'
        );
        $stmt->execute(['equipement_id' => $equipementId]);
        return $stmt->fetchAll();
    }

    /**
     * Return the number of units still available for booking during the
     * requested date window (i.e. stock minus already-booked quantity).
     * Useful for building informative error messages in the controller.
     */
    public function availableForPeriod(
        int    $equipementId,
        string $debut,
        string $fin,
        ?int   $exceptId = null
    ): int {
        $sql = "SELECT COALESCE(SUM(l.quantite), 0) AS total_reserved
                FROM location l
                WHERE l.equipement_id = :equipement_id
                  AND l.statut IN ('en attente', 'confirmee', 'en cours')
                  AND l.date_debut <= :date_fin
                  AND l.date_fin   >= :date_debut";

        $params = [
            'equipement_id' => $equipementId,
            'date_debut'    => $debut,
            'date_fin'      => $fin,
        ];
        if ($exceptId !== null) {
            $sql .= ' AND l.id != :except_id';
            $params['except_id'] = $exceptId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $totalReserved = (int) $stmt->fetchColumn();

        $stockStmt = $this->db->prepare(
            'SELECT quantite_stock FROM equipement WHERE id = :id'
        );
        $stockStmt->execute(['id' => $equipementId]);
        $quantiteStock = (int) $stockStmt->fetchColumn();

        return max(0, $quantiteStock - $totalReserved);
    }

    /**
     * Check whether the requested quantity can be accommodated for the given
     * equipment over the requested date window.
     *
     * Logic:
     *   total_reserved = SUM(quantite) of active locations that overlap the window
     *   available      = quantite_stock - total_reserved
     *   blocked        = requested_qty > available
     *
     * Returns TRUE  → booking is blocked (not enough stock for this period).
     * Returns FALSE → booking is allowed.
     *
     * @param int         $equipementId  Equipment to check.
     * @param string      $debut         Start date (Y-m-d).
     * @param string      $fin           End date   (Y-m-d).
     * @param int         $requestedQty  Quantity the client wants to book.
     * @param int|null    $exceptId      Location row to exclude (useful for edits).
     */
    public function hasOverlap(
        int     $equipementId,
        string  $debut,
        string  $fin,
        int     $requestedQty = 1,
        ?int    $exceptId     = null
    ): bool {
        // ── 1. Sum quantities already booked in the overlapping window ────────
        $sql = "SELECT COALESCE(SUM(l.quantite), 0) AS total_reserved
                FROM location l
                WHERE l.equipement_id = :equipement_id
                  AND l.statut IN ('en attente', 'confirmee', 'en cours')
                  AND l.date_debut <= :date_fin
                  AND l.date_fin   >= :date_debut";

        $params = [
            'equipement_id' => $equipementId,
            'date_debut'    => $debut,
            'date_fin'      => $fin,
        ];

        if ($exceptId !== null) {
            $sql .= ' AND l.id != :except_id';
            $params['except_id'] = $exceptId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $totalReserved = (int) $stmt->fetchColumn();

        // ── 2. Fetch current physical stock ───────────────────────────────────
        $stockStmt = $this->db->prepare(
            'SELECT quantite_stock FROM equipement WHERE id = :id'
        );
        $stockStmt->execute(['id' => $equipementId]);
        $quantiteStock = (int) $stockStmt->fetchColumn();

        // ── 3. Decide ─────────────────────────────────────────────────────────
        $available = $quantiteStock - $totalReserved;
        return $requestedQty > $available;  // TRUE = blocked
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO location
                (date_debut, date_fin, statut, montant_total, frais_additionnels, utilisateur_id, equipement_id, quantite)
             VALUES
                (:date_debut, :date_fin, :statut, :montant_total, :frais_additionnels, :utilisateur_id, :equipement_id, :quantite)'
        );
        $stmt->execute([
            'date_debut'          => $data['date_debut'],
            'date_fin'            => $data['date_fin'],
            'statut'              => $data['statut'] ?? 'en attente',
            'montant_total'       => $data['montant_total'],
            'frais_additionnels'  => $data['frais_additionnels'] ?? 0,
            'utilisateur_id'      => $data['utilisateur_id'],
            'equipement_id'       => $data['equipement_id'],
            'quantite'            => max(1, (int) ($data['quantite'] ?? 1)),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE location SET
                date_debut           = :date_debut,
                date_fin             = :date_fin,
                statut               = :statut,
                montant_total        = :montant_total,
                frais_additionnels   = :frais_additionnels,
                utilisateur_id       = :utilisateur_id,
                equipement_id        = :equipement_id,
                quantite             = :quantite
             WHERE id = :id'
        );
        return $stmt->execute([
            'date_debut'         => $data['date_debut'],
            'date_fin'           => $data['date_fin'],
            'statut'             => $data['statut'],
            'montant_total'      => $data['montant_total'],
            'frais_additionnels' => $data['frais_additionnels'] ?? 0,
            'utilisateur_id'     => $data['utilisateur_id'],
            'equipement_id'      => $data['equipement_id'],
            'quantite'           => max(1, (int) ($data['quantite'] ?? 1)),
            'id'                 => $id,
        ]);
    }

    public function updateStatut(int $id, string $statut): bool
    {
        $stmt = $this->db->prepare('UPDATE location SET statut = :statut WHERE id = :id');
        return $stmt->execute(['statut' => $statut, 'id' => $id]);
    }

    public function updateFrais(int $id, float $frais): bool
    {
        $location = $this->findById($id);
        if ($location === null) {
            return false;
        }
        $equipement = (new Equipement($this->db))->findById((int) $location['equipement_id']);
        $jours = days_between($location['date_debut'], $location['date_fin']);
        $montant = ((float) $equipement['prix_jour'] * $jours) + $frais;

        $stmt = $this->db->prepare(
            'UPDATE location SET frais_additionnels = :frais, montant_total = :montant WHERE id = :id'
        );
        return $stmt->execute([
            'frais' => $frais,
            'montant' => $montant,
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM location WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function calculerMontant(float $prixJour, string $debut, string $fin, float $frais = 0): float
    {
        return ($prixJour * days_between($debut, $fin)) + $frais;
    }

    public function countByStatut(): array
    {
        $stmt = $this->db->query(
            'SELECT statut, COUNT(*) AS total FROM location GROUP BY statut ORDER BY statut'
        );
        return $stmt->fetchAll();
    }

    public function chiffreAffaires(): float
    {
        $stmt = $this->db->query(
            "SELECT COALESCE(SUM(montant_total), 0) AS total
             FROM location
             WHERE statut IN ('confirmee', 'en cours', 'terminee')"
        );
        return (float) $stmt->fetchColumn();
    }

    private function baseSelect(): string
    {
        return 'SELECT l.*,
                       l.quantite,
                       u.nom AS utilisateur_nom,
                       u.prenom AS utilisateur_prenom,
                       u.email AS utilisateur_email,
                       e.nom AS equipement_nom,
                       e.prix_jour,
                       e.quantite_stock,
                       e.etat AS equipement_etat
                FROM location l
                INNER JOIN utilisateur u ON u.id = l.utilisateur_id
                INNER JOIN equipement e ON e.id = l.equipement_id';
    }
}
