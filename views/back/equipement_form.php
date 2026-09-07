<?php
$title  = ($equipement !== null) ? 'Modifier un équipement' : 'Nouvel équipement';
$isEdit = ($equipement !== null);
require APP_ROOT . '/views/layout/admin_header.php';
?>

<div class="panel" style="margin-bottom:1.25rem">
    <h2 style="font-size:1.05rem;margin:0">
        <?= $isEdit
            ? '✏️ Modifier l\'équipement #' . (int) $equipement['id']
            : '➕ Ajouter un nouvel équipement' ?>
    </h2>
</div>

<!-- ONE unified form — handles text fields AND photo in the same POST -->
<form method="post"
      action="<?= e(url('admin/equipement/save')) ?>"
      class="form panel"
      enctype="multipart/form-data">

    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) ($equipement['id'] ?? 0) ?>">

    <!-- ── Nom ───────────────────────────────────────────────── -->
    <label>
        Nom <span style="color:var(--bad)">*</span>
        <input type="text"
               name="nom"
               value="<?= e($equipement['nom'] ?? '') ?>"
               required
               maxlength="150"
               placeholder="Ex : Perceuse percussion 18V">
    </label>

    <!-- ── Description ───────────────────────────────────────── -->
    <label>
        Description
        <textarea name="description"
                  rows="4"
                  placeholder="Caractéristiques, accessoires inclus…"><?= e($equipement['description'] ?? '') ?></textarea>
    </label>

    <!-- ── Prix / Stock / Seuil ──────────────────────────────── -->
    <div class="form-row">
        <label>
            Prix / jour (€) <span style="color:var(--bad)">*</span>
            <input type="number"
                   name="prix_jour"
                   value="<?= e($equipement['prix_jour'] ?? '') ?>"
                   step="0.01"
                   min="0.01"
                   required
                   placeholder="0.00">
        </label>
        <label>
            Quantité en stock
            <input type="number"
                   name="quantite_stock"
                   value="<?= (int) ($equipement['quantite_stock'] ?? 0) ?>"
                   min="0"
                   required>
        </label>
        <label>
            Seuil d'alerte
            <input type="number"
                   name="seuil_alerte"
                   value="<?= (int) ($equipement['seuil_alerte'] ?? 1) ?>"
                   min="0"
                   required>
        </label>
    </div>

    <!-- ── État + Catégorie ───────────────────────────────────── -->
    <div class="form-row">
        <label>
            État <span style="color:var(--bad)">*</span>
            <select name="etat" required>
                <?php foreach (EQUIPEMENT_ETATS as $opt): ?>
                    <option value="<?= e($opt) ?>"
                        <?= (($equipement['etat'] ?? 'disponible') === $opt) ? 'selected' : '' ?>>
                        <?= e(etat_label($opt)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Catégorie <span style="color:var(--bad)">*</span>
            <select name="categorie_id" required>
                <option value="">— Choisir —</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>"
                        <?= ((int) ($equipement['categorie_id'] ?? 0) === (int) $cat['id']) ? 'selected' : '' ?>>
                        <?= e($cat['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>

    <!-- ── Photo ─────────────────────────────────────────────── -->
    <fieldset style="border:1px solid var(--line);border-radius:var(--radius-sm);padding:1.1rem 1.25rem">
        <legend style="padding:0 .5rem;font-size:.78rem;font-weight:700;
                        text-transform:uppercase;letter-spacing:.06em;color:var(--muted)">
            Photo
        </legend>

        <?php if ($isEdit && !empty($equipement['photo'])): ?>
            <!-- Current image preview + delete option -->
            <div style="display:flex;align-items:flex-start;gap:1.1rem;margin-bottom:1rem;flex-wrap:wrap">
                <img src="<?= e(url($equipement['photo'])) ?>"
                     alt="Photo actuelle"
                     id="current-photo-preview"
                     style="width:130px;height:98px;object-fit:cover;
                            border-radius:var(--radius-xs);border:1px solid var(--line);
                            flex-shrink:0">
                <div style="display:flex;flex-direction:column;gap:.5rem;justify-content:center">
                    <p style="font-size:.82rem;color:var(--muted)">Image actuelle</p>
                    <label style="display:flex;align-items:center;gap:.5rem;
                                  font-size:.875rem;font-weight:600;
                                  color:var(--bad);cursor:pointer;
                                  background:var(--bad-bg);
                                  border:1px solid rgba(244,63,94,.25);
                                  border-radius:var(--radius-xs);
                                  padding:.35rem .75rem;
                                  width:fit-content">
                        <input type="checkbox"
                               name="delete_image"
                               value="1"
                               id="delete-image-cb"
                               style="width:auto;accent-color:var(--bad)">
                        Supprimer la photo actuelle
                    </label>
                </div>
            </div>
        <?php endif; ?>

        <!-- File picker — always shown (create and edit) -->
        <label style="display:inline-flex;flex-direction:column;gap:.4rem;
                       font-size:.82rem;font-weight:700;
                       text-transform:uppercase;letter-spacing:.05em;color:var(--muted);
                       cursor:pointer">
            <?= ($isEdit && !empty($equipement['photo'])) ? 'Remplacer par une nouvelle image' : 'Choisir une image' ?>
            <input type="file"
                   name="image"
                   id="image-upload"
                   accept="image/jpeg,image/png,image/webp,image/gif"
                   style="font-size:.875rem;color:var(--ink);cursor:pointer">
        </label>
        <p class="hint" style="margin-top:.4rem">JPEG, PNG, WebP ou GIF · max 2 Mo</p>

        <!-- Live preview of newly selected file -->
        <img id="new-photo-preview"
             src=""
             alt="Aperçu"
             style="display:none;
                    width:130px;height:98px;object-fit:cover;
                    border-radius:var(--radius-xs);border:1px solid var(--line);
                    margin-top:.75rem">
    </fieldset>

    <!-- ── Actions ───────────────────────────────────────────── -->
    <div style="display:flex;gap:.75rem;align-items:center;margin-top:.25rem;flex-wrap:wrap">
        <button class="btn btn-primary" type="submit">
            <?= $isEdit ? '💾 Enregistrer les modifications' : '➕ Ajouter l\'équipement' ?>
        </button>
        <a class="btn btn-ghost" href="<?= e(url('admin/equipements')) ?>">Annuler</a>
    </div>

</form>

<script>
(function () {
    const fileInput     = document.getElementById('image-upload');
    const newPreview    = document.getElementById('new-photo-preview');
    const currentPhoto  = document.getElementById('current-photo-preview');
    const deleteCheckbox = document.getElementById('delete-image-cb');

    // Show a live preview when a new file is picked
    fileInput?.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            newPreview.src = URL.createObjectURL(this.files[0]);
            newPreview.style.display = 'block';
            // If a new file is chosen, uncheck "delete current"
            if (deleteCheckbox) deleteCheckbox.checked = false;
        } else {
            newPreview.style.display = 'none';
        }
    });

    // If "delete current" is checked, dim the current preview
    deleteCheckbox?.addEventListener('change', function () {
        if (currentPhoto) {
            currentPhoto.style.opacity = this.checked ? '0.3' : '1';
        }
        // Clear file input when delete is checked to avoid confusing state
        if (this.checked && fileInput) {
            fileInput.value = '';
            newPreview.style.display = 'none';
        }
    });
})();
</script>

<?php require APP_ROOT . '/views/layout/admin_footer.php'; ?>
