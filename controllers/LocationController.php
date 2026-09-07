<?php

class LocationController
{
    private Location   $location;
    private Equipement $equipement;

    public function __construct()
    {
        $this->location   = new Location();
        $this->equipement = new Equipement();
    }

    // ── Front-office ──────────────────────────────────────────────────────────

    public function home(): void
    {
        $categories  = (new Categorie())->findAll();
        $equipements = $this->equipement->findDisponibles();
        view('front/home', [
            'categories'  => $categories,
            'equipements' => array_slice($equipements, 0, 6),
        ]);
    }

    public function mesLocations(): void
    {
        require_login();
        view('front/mes_locations', [
            'locations' => $this->location->findByUtilisateur((int) current_user()['id']),
        ]);
    }

    public function createForm(int $equipementId): void
    {
        require_login();
        $item = $this->equipement->findById($equipementId);
        if ($item === null) {
            flash('error', 'Équipement introuvable.');
            redirect('equipements');
        }
        if ($item['etat'] !== 'disponible' || (int) $item['quantite_stock'] < 1) {
            flash('error', 'Cet équipement n\'est pas disponible à la location.');
            redirect('equipements/show/' . $equipementId);
        }

        view('front/location_create', ['equipement' => $item]);
    }

    public function store(): void
    {
        require_login();
        if (!verify_csrf()) {
            flash('error', 'Jeton de sécurité invalide.');
            redirect('equipements');
        }

        $equipementId = (int) ($_POST['equipement_id'] ?? 0);
        $item = $this->equipement->findById($equipementId);
        if ($item === null) {
            flash('error', 'Équipement introuvable.');
            redirect('equipements');
        }

        // ── Quantity: basic bounds check ──────────────────────────────────────
        $quantite   = max(1, (int) ($_POST['quantite'] ?? 1));
        $stockTotal = (int) $item['quantite_stock'];

        if ($quantite > $stockTotal) {
            flash('error', "Stock insuffisant. Seulement {$stockTotal} unité(s) disponible(s) au total.");
            redirect('locations/create/' . $equipementId);
        }

        // ── Date validation ───────────────────────────────────────────────────
        $debut = $_POST['date_debut'] ?? '';
        $fin   = $_POST['date_fin']   ?? '';

        if (!$this->datesValides($debut, $fin)) {
            flash('error', 'Les dates de location sont invalides.');
            redirect('locations/create/' . $equipementId);
        }

        // ── Overlap / period-availability check ───────────────────────────────
        // hasOverlap() sums quantities already booked over the same date window
        // and blocks the request if requested qty exceeds what remains.
        if ($this->location->hasOverlap($equipementId, $debut, $fin, $quantite)) {
            $dispo = $this->location->availableForPeriod($equipementId, $debut, $fin);
            if ($dispo === 0) {
                flash('error', 'Cet équipement n\'est plus disponible sur cette période.');
            } else {
                flash('error', "Stock insuffisant pour cette période. Disponibilité restante\u{00A0}: {$dispo} unité(s).");
            }
            redirect('locations/create/' . $equipementId);
        }

        // ── Create — status stays 'en attente', stock NOT touched yet ─────────
        $montant = $this->location->calculerMontant(
            (float) $item['prix_jour'], $debut, $fin
        );

        $this->location->create([
            'date_debut'         => $debut,
            'date_fin'           => $fin,
            'statut'             => 'en attente',
            'montant_total'      => $montant,
            'frais_additionnels' => 0,
            'utilisateur_id'     => current_user()['id'],
            'equipement_id'      => $equipementId,
            'quantite'           => $quantite,
        ]);

        flash('success', 'Demande de location enregistrée. Un agent confirmera votre réservation.');
        redirect('mes-locations');
    }

    public function cancel(): void
    {
        require_login();
        if (!verify_csrf()) {
            flash('error', 'Jeton de sécurité invalide.');
            redirect('mes-locations');
        }

        $id  = (int) ($_POST['id'] ?? 0);
        $row = $this->location->findById($id);

        if ($row === null || (int) $row['utilisateur_id'] !== (int) current_user()['id']) {
            flash('error', 'Location introuvable.');
            redirect('mes-locations');
        }

        if (!in_array($row['statut'], ['en attente', 'confirmee'], true)) {
            flash('error', 'Cette location ne peut plus être annulée.');
            redirect('mes-locations');
        }

        $this->location->updateStatut($id, 'annulee');

        // Only restore stock if the location was already confirmed (stock was deducted)
        if (in_array($row['statut'], ['confirmee', 'en cours'], true)) {
            $this->restoreStock((int) $row['equipement_id'], (int) $row['quantite']);
        }

        flash('success', 'Location annulée.');
        redirect('mes-locations');
    }

    // ── Back-office ───────────────────────────────────────────────────────────

    public function adminIndex(): void
    {
        require_staff();
        $statut = $_GET['statut'] ?? null;
        view('back/locations', [
            'locations' => $this->location->findAll($statut ?: null),
            'statut'    => $statut,
        ]);
    }

    public function updateStatut(): void
    {
        require_staff();
        if (!verify_csrf()) {
            flash('error', 'Jeton de sécurité invalide.');
            redirect('admin/locations');
        }

        $id     = (int) ($_POST['id']     ?? 0);
        $statut = $_POST['statut']        ?? '';
        $row    = $this->location->findById($id);

        if ($row === null || !in_array($statut, LOCATION_STATUTS, true)) {
            flash('error', 'Données invalides.');
            redirect('admin/locations');
        }

        $ancien       = $row['statut'];
        $equipementId = (int) $row['equipement_id'];
        $quantite     = (int) $row['quantite'];

        // ── Save new status ───────────────────────────────────────────────────
        $this->location->updateStatut($id, $statut);

        // ── Frais additionnels ────────────────────────────────────────────────
        if (isset($_POST['frais_additionnels']) && $_POST['frais_additionnels'] !== '') {
            $this->location->updateFrais($id, (float) $_POST['frais_additionnels']);
        }

        // ── Stock rules ───────────────────────────────────────────────────────
        //
        // CONFIRM (en attente → confirmée | en cours):
        //   Deduct rented quantity from stock.
        //   If remaining stock = 0 → etat = 'en location'
        //   If remaining stock > 0 → keep etat = 'disponible'
        //
        // CLOSE   (confirmée | en cours → terminée | annulée):
        //   Add rented quantity back to stock.
        //   If etat was 'en location' and stock is now > 0 → reset etat = 'disponible'
        //
        // Any other transition (e.g. en attente → annulée): no stock change.

        $isConfirming = in_array($statut, ['confirmee', 'en cours'], true)
                     && !in_array($ancien, ['confirmee', 'en cours'], true);

        $isClosing    = in_array($statut, ['terminee', 'annulee'], true)
                     && in_array($ancien, ['confirmee', 'en cours'], true);

        if ($isConfirming) {
            $this->equipement->decrementStock($equipementId, $quantite);
            // Re-fetch fresh stock after decrement
            $fresh = $this->equipement->findById($equipementId);
            $newStock = (int) $fresh['quantite_stock'];
            $this->equipement->updateEtat(
                $equipementId,
                $newStock === 0 ? 'en location' : 'disponible'
            );
        }

        if ($isClosing) {
            $this->restoreStock($equipementId, $quantite);
        }

        flash('success', 'Location mise à jour.');
        redirect('admin/locations');
    }

    public function dashboard(): void
    {
        require_staff();

        // ── Statut counts ─────────────────────────────────────────────────────
        // DB values are now clean ASCII — direct key lookup works.
        $statutRows = $this->location->countByStatut();
        $statutMap  = [];
        foreach ($statutRows as $r) {
            $statutMap[$r['statut']] = (int) $r['total'];
        }

        // ── Etat counts ───────────────────────────────────────────────────────
        $etatRows = $this->equipement->countByEtat();
        $etatMap  = [];
        foreach ($etatRows as $r) {
            $etatMap[$r['etat']] = (int) $r['total'];
        }

        // ── Build display totals keyed by canonical normalised strings ─────────
        // The view iterates LOCATION_STATUTS / EQUIPEMENT_ETATS constants and
        // looks up counts with normalize_status() so the key always matches.
        view('back/dashboard', [
            'alertes'       => $this->equipement->findLowStock(),
            'etats'         => $etatRows,
            'etatMap'       => $etatMap,
            'statuts'       => $statutRows,
            'statutMap'     => $statutMap,
            'ca'            => $this->location->chiffreAffaires(),
            'locations'     => array_slice($this->location->findAll(), 0, 8),
            'nbEquipements' => count($this->equipement->findAll()),
            'nbUsers'       => count((new Utilisateur())->findAll()),
        ]);
    }

    public function pdf(int $id): void
    {
        require_login();

        $row = $this->location->findById($id);
        if ($row === null) {
            flash('error', 'Location introuvable.');
            redirect('mes-locations');
        }

        if (!has_role('agent', 'responsable')
            && (int) $row['utilisateur_id'] !== (int) current_user()['id']) {
            flash('error', 'Accès refusé.');
            redirect('mes-locations');
        }

        view('front/location_pdf', ['location' => $row]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Add $quantite back to stock and reset etat to 'disponible'
     * if the equipment was 'en location' (stock had hit 0).
     */
    private function restoreStock(int $equipementId, int $quantite): void
    {
        $this->equipement->incrementStock($equipementId, $quantite);
        $fresh    = $this->equipement->findById($equipementId);
        $newStock = (int) $fresh['quantite_stock'];
        $curEtat  = $fresh['etat'];

        // Reset to disponible only if the equipment is currently marked 'en location'
        // (never overwrite 'en maintenance' or 'endommagé' automatically)
        if ($newStock > 0 && $curEtat === 'en location') {
            $this->equipement->updateEtat($equipementId, 'disponible');
        }
    }

    private function datesValides(string $debut, string $fin): bool
    {
        if ($debut === '' || $fin === '') {
            return false;
        }
        try {
            $from = new DateTime($debut);
            $to   = new DateTime($fin);
        } catch (Exception $e) {
            return false;
        }
        $today = new DateTime('today');
        return $from >= $today && $to >= $from;
    }
}
