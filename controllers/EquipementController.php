<?php

class EquipementController
{
    private Equipement $equipement;
    private Categorie  $categorie;

    // Allowed MIME types and max file size for equipment photos
    private const ALLOWED_MIME  = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2 MB
    private const UPLOAD_DIR    = PUBLIC_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

    public function __construct()
    {
        $this->equipement = new Equipement();
        $this->categorie  = new Categorie();
    }

    // ── Front-office ─────────────────────────────────────────────────────────

    public function catalogue(): void
    {
        // --- Category filter ---
        // GET param comes in as a string; empty string / absent → show all categories
        $catRaw      = trim($_GET['categorie'] ?? '');
        $categorieId = ($catRaw !== '' && ctype_digit($catRaw)) ? (int) $catRaw : null;

        // --- État filter ---
        // Default to 'disponible' on first load; 'all' means no état filter
        $etatRaw = trim($_GET['etat'] ?? 'disponible');
        if ($etatRaw === 'all') {
            $etat = null;         // no WHERE etat clause
        } elseif (in_array($etatRaw, EQUIPEMENT_ETATS, true)) {
            $etat = $etatRaw;     // valid enum value
        } else {
            $etat    = null;      // unknown value → show all
            $etatRaw = 'all';
        }

        // --- Run the query with whatever filters are active ---
        $equipements = $this->equipement->findAll($categorieId, $etat);

        view('front/equipements', [
            'equipements' => $equipements,
            'categories'  => $this->categorie->findAll(),
            'categorieId' => $categorieId,   // int|null  — used to mark <option selected>
            'etat'        => $etatRaw,        // string    — 'disponible' | 'all' | enum value
            'totalCount'  => count($equipements),
            'isFiltered'  => ($categorieId !== null || $etatRaw !== 'disponible'),
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

    // ── Back-office ───────────────────────────────────────────────────────────

    public function adminIndex(): void
    {
        require_staff();
        view('back/equipements', [
            'equipements' => $this->equipement->findAll(),
            'alertes'     => $this->equipement->findLowStock(),
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

        // Collect and sanitise
        $nom          = trim($_POST['nom']            ?? '');
        $description  = trim($_POST['description']    ?? '');
        $prixJour     = (float) ($_POST['prix_jour']  ?? 0);
        $stock        = max(0, (int) ($_POST['quantite_stock'] ?? 0));
        $seuil        = max(0, (int) ($_POST['seuil_alerte']   ?? 1));
        $etat         = $_POST['etat']         ?? 'disponible';
        $categorieId  = (int) ($_POST['categorie_id'] ?? 0);

        // Validate
        if ($nom === '') {
            flash('error', 'Le nom est obligatoire.');
            redirect($id > 0 ? 'admin/equipement/edit/' . $id : 'admin/equipement/add');
        }
        if ($prixJour <= 0) {
            flash('error', 'Le prix journalier doit être supérieur à 0.');
            redirect($id > 0 ? 'admin/equipement/edit/' . $id : 'admin/equipement/add');
        }
        if ($categorieId <= 0) {
            flash('error', 'Veuillez choisir une catégorie.');
            redirect($id > 0 ? 'admin/equipement/edit/' . $id : 'admin/equipement/add');
        }
        if (!in_array($etat, EQUIPEMENT_ETATS, true)) {
            $etat = 'disponible';
        }

        // Photo — optional, never blocks save
        $photo = null;
        if ($id > 0) {
            $existing = $this->equipement->findById($id);
            $photo    = $existing['photo'] ?? null;
        }
        if (isset($_POST['remove_photo']) && $_POST['remove_photo'] === '1') {
            $this->deletePhotoFile($photo);
            $photo = null;
        }
        $photoResult = $this->handlePhotoUpload($id);
        if ($photoResult['error'] === null && $photoResult['path'] !== null) {
            $photo = $photoResult['path'];
        }

        $data = [
            'nom'            => $nom,
            'description'    => $description,
            'prix_jour'      => $prixJour,
            'quantite_stock' => $stock,
            'seuil_alerte'   => $seuil,
            'etat'           => $etat,
            'categorie_id'   => $categorieId,
            'photo'          => $photo,
        ];

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
            try {
                // Remove the photo file from disk before deleting the DB row
                $item = $this->equipement->findById($id);
                $this->equipement->delete($id);
                $this->deletePhotoFile($item['photo'] ?? null);
                flash('success', 'Équipement supprimé.');
            } catch (PDOException $e) {
                // FK constraint fires when active rentals reference this equipment
                flash('error', 'Impossible de supprimer : cet équipement est lié à des locations existantes.');
            }
        }

        redirect('admin/equipements');
    }

    // ── Categories ────────────────────────────────────────────────────────────

    public function categories(): void
    {
        require_staff();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf()) {
                flash('error', 'Jeton de sécurité invalide.');
                redirect('admin/categories');
            }

            $action = $_POST['action'] ?? 'save';
            $id     = (int) ($_POST['id'] ?? 0);

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
                'nom'         => trim($_POST['nom'] ?? ''),
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
            'edit'       => isset($_GET['edit']) ? $this->categorie->findById((int) $_GET['edit']) : null,
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Handles the $_FILES['photo'] upload.
     * Returns ['path' => string|null, 'error' => string|null].
     * 'path' is null when no file was submitted (not an error).
     */
    private function handlePhotoUpload(int $equipementId): array
    {
        $file = $_FILES['photo'] ?? null;

        // No file field submitted or empty upload — not an error
        if ($file === null || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['path' => null, 'error' => null];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['path' => null, 'error' => 'Erreur lors du téléversement de l\'image (code ' . $file['error'] . ').'];
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            return ['path' => null, 'error' => 'L\'image ne doit pas dépasser 2 Mo.'];
        }

        // Validate MIME via finfo (not Content-Type which is user-controlled)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            return ['path' => null, 'error' => 'Format d\'image non autorisé. Utilisez JPEG, PNG, WebP ou GIF.'];
        }

        // Build a safe, unique filename
        $ext      = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            default      => 'jpg',
        };
        $filename = 'equip_' . ($equipementId ?: uniqid('', true)) . '_' . time() . '.' . $ext;
        $dest     = self::UPLOAD_DIR . $filename;

        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return ['path' => null, 'error' => 'Impossible d\'enregistrer le fichier sur le serveur.'];
        }

        // Return path relative to public/ so url() can build the src
        return ['path' => 'uploads/' . $filename, 'error' => null];
    }

    /**
     * Deletes a photo file from disk given its relative path (e.g. "uploads/equip_3_xxx.jpg").
     * Silently ignores null / missing files.
     */
    private function deletePhotoFile(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }
        $abs = PUBLIC_PATH . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);
        if (is_file($abs)) {
            @unlink($abs);
        }
    }
}
