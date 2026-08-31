<?php

class LocationController
{
    private Location $location;
    private Equipement $equipement;

    public function __construct()
    {
        $this->location = new Location();
        $this->equipement = new Equipement();
    }

    public function home(): void
    {
        $categories = (new Categorie())->findAll();
        $equipements = $this->equipement->findDisponibles();
        view('front/home', [
            'categories' => $categories,
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

        $debut = $_POST['date_debut'] ?? '';
        $fin = $_POST['date_fin'] ?? '';

        if (!$this->datesValides($debut, $fin)) {
            flash('error', 'Les dates de location sont invalides.');
            redirect('locations/create/' . $equipementId);
        }

        if ($this->location->hasOverlap($equipementId, $debut, $fin)) {
            flash('error', 'Cet équipement est déjà réservé sur cette période.');
            redirect('locations/create/' . $equipementId);
        }

        $montant = $this->location->calculerMontant((float) $item['prix_jour'], $debut, $fin);

        $this->location->create([
            'date_debut' => $debut,
            'date_fin' => $fin,
            'statut' => 'en attente',
            'montant_total' => $montant,
            'frais_additionnels' => 0,
            'utilisateur_id' => current_user()['id'],
            'equipement_id' => $equipementId,
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

        $id = (int) ($_POST['id'] ?? 0);
        $row = $this->location->findById($id);
        if ($row === null || (int) $row['utilisateur_id'] !== (int) current_user()['id']) {
            flash('error', 'Location introuvable.');
            redirect('mes-locations');
        }

        if (!in_array($row['statut'], ['en attente', 'confirmée'], true)) {
            flash('error', 'Cette location ne peut plus être annulée.');
            redirect('mes-locations');
        }

        $this->location->updateStatut($id, 'annulée');
        if ($row['statut'] === 'confirmée' || $row['statut'] === 'en cours') {
            $this->equipement->incrementStock((int) $row['equipement_id']);
            $this->equipement->updateEtat((int) $row['equipement_id'], 'disponible');
        }

        flash('success', 'Location annulée.');
        redirect('mes-locations');
    }

    public function adminIndex(): void
    {
        require_staff();
        $statut = $_GET['statut'] ?? null;
        view('back/locations', [
            'locations' => $this->location->findAll($statut ?: null),
            'statut' => $statut,
        ]);
    }

    public function updateStatut(): void
    {
        require_staff();
        if (!verify_csrf()) {
            flash('error', 'Jeton de sécurité invalide.');
            redirect('admin/locations');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $statut = $_POST['statut'] ?? '';
        $row = $this->location->findById($id);

        if ($row === null || !in_array($statut, LOCATION_STATUTS, true)) {
            flash('error', 'Données invalides.');
            redirect('admin/locations');
        }

        $ancien = $row['statut'];
        $this->location->updateStatut($id, $statut);

        $equipementId = (int) $row['equipement_id'];
        if (in_array($statut, ['confirmée', 'en cours'], true) && !in_array($ancien, ['confirmée', 'en cours'], true)) {
            $this->equipement->decrementStock($equipementId);
            $this->equipement->updateEtat($equipementId, 'en location');
        }
        if (in_array($statut, ['terminée', 'annulée'], true) && in_array($ancien, ['confirmée', 'en cours'], true)) {
            $this->equipement->incrementStock($equipementId);
            $this->equipement->updateEtat($equipementId, 'disponible');
        }

        $frais = isset($_POST['frais_additionnels']) ? (float) $_POST['frais_additionnels'] : null;
        if ($frais !== null) {
            $this->location->updateFrais($id, $frais);
        }

        flash('success', 'Location mise à jour.');
        redirect('admin/locations');
    }

    public function dashboard(): void
    {
        require_staff();
        $equipement = $this->equipement;
        view('back/dashboard', [
            'alertes' => $equipement->findLowStock(),
            'etats' => $equipement->countByEtat(),
            'statuts' => $this->location->countByStatut(),
            'ca' => $this->location->chiffreAffaires(),
            'locations' => array_slice($this->location->findAll(), 0, 8),
            'nbEquipements' => count($equipement->findAll()),
            'nbUsers' => count((new Utilisateur())->findAll()),
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

        // Clients can only download their own contracts; staff can download any
        if (!has_role('agent', 'responsable') && (int) $row['utilisateur_id'] !== (int) current_user()['id']) {
            flash('error', 'Accès refusé.');
            redirect('mes-locations');
        }

        view('front/location_pdf', ['location' => $row]);
    }

    private function datesValides(string $debut, string $fin): bool
    {
        if ($debut === '' || $fin === '') {
            return false;
        }
        try {
            $from = new DateTime($debut);
            $to = new DateTime($fin);
        } catch (Exception $e) {
            return false;
        }
        $today = new DateTime('today');
        return $from >= $today && $to >= $from;
    }
}
