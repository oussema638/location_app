<?php

class EquipementController
{
    private Equipement $equipement;
    private Categorie $categorie;

    public function __construct()
    {
        $this->equipement = new Equipement();
        $this->categorie = new Categorie();
    }

    public function catalogue(): void
    {
        $categorieId = isset($_GET['categorie']) ? (int) $_GET['categorie'] : null;
        $etat = $_GET['etat'] ?? 'disponible';
        if ($etat === 'all') {
            $etat = null;
        }

        view('front/equipements', [
            'equipements' => $this->equipement->findAll($categorieId ?: null, $etat),
            'categories' => $this->categorie->findAll(),
            'categorieId' => $categorieId,
            'etat' => $etat ?? 'all',
        ]);
    }

    public function show(int $id): void
    {
        $item = $this->equipement->findById($id);
        if ($item === null) {
            http_response_code(404);
            flash('error', 'Équipement introuvable.');
            redirect('equipements');
        }

        view('front/equipement_detail', ['equipement' => $item]);
    }

    public function adminIndex(): void
    {
        require_staff();
        view('back/equipements', [
            'equipements' => $this->equipement->findAll(),
            'alertes' => $this->equipement->findLowStock(),
        ]);
    }

    public function adminForm(?int $id = null): void
    {
        require_staff();
        $item = $id ? $this->equipement->findById($id) : null;
        if ($id && $item === null) {
            flash('error', 'Équipement introuvable.');
            redirect('admin/equipements');
        }

        view('back/equipement_form', [
            'equipement' => $item,
            'categories' => $this->categorie->findAll(),
        ]);
    }

    public function save(): void
    {
        require_staff();
        if (!verify_csrf()) {
            flash('error', 'Jeton de sécurité invalide.');
            redirect('admin/equipements');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $data = [
            'nom' => trim($_POST['nom'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'prix_jour' => (float) ($_POST['prix_jour'] ?? 0),
            'quantite_stock' => (int) ($_POST['quantite_stock'] ?? 0),
            'seuil_alerte' => (int) ($_POST['seuil_alerte'] ?? 1),
            'etat' => $_POST['etat'] ?? 'disponible',
            'categorie_id' => (int) ($_POST['categorie_id'] ?? 0),
        ];

        if ($data['nom'] === '' || $data['prix_jour'] <= 0 || $data['categorie_id'] <= 0) {
            flash('error', 'Nom, prix journalier et catégorie sont obligatoires.');
            redirect($id ? 'admin/equipements/edit/' . $id : 'admin/equipements/create');
        }

        if (!in_array($data['etat'], EQUIPEMENT_ETATS, true)) {
            $data['etat'] = 'disponible';
        }

        if ($id > 0) {
            $this->equipement->update($id, $data);
            flash('success', 'Équipement mis à jour.');
        } else {
            $this->equipement->create($data);
            flash('success', 'Équipement ajouté.');
        }

        redirect('admin/equipements');
    }

    public function delete(): void
    {
        require_staff();
        if (!verify_csrf()) {
            flash('error', 'Jeton de sécurité invalide.');
            redirect('admin/equipements');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->equipement->delete($id);
            flash('success', 'Équipement supprimé.');
        }
        redirect('admin/equipements');
    }

    public function categories(): void
    {
        require_staff();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf()) {
                flash('error', 'Jeton de sécurité invalide.');
                redirect('admin/categories');
            }

            $action = $_POST['action'] ?? 'save';
            $id = (int) ($_POST['id'] ?? 0);

            if ($action === 'delete' && $id > 0) {
                try {
                    $this->categorie->delete($id);
                    flash('success', 'Catégorie supprimée.');
                } catch (PDOException $e) {
                    flash('error', 'Impossible de supprimer : des équipements y sont encore liés.');
                }
                redirect('admin/categories');
            }

            $data = [
                'nom' => trim($_POST['nom'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
            ];
            if ($data['nom'] === '') {
                flash('error', 'Le nom de la catégorie est obligatoire.');
                redirect('admin/categories');
            }

            if ($id > 0) {
                $this->categorie->update($id, $data);
                flash('success', 'Catégorie mise à jour.');
            } else {
                $this->categorie->create($data);
                flash('success', 'Catégorie créée.');
            }
            redirect('admin/categories');
        }

        view('back/categories', [
            'categories' => $this->categorie->findAll(),
            'edit' => isset($_GET['edit']) ? $this->categorie->findById((int) $_GET['edit']) : null,
        ]);
    }
}
