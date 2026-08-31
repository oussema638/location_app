<?php
$title  = ($equipement !== null) ? 'Modifier un équipement' : 'Nouvel équipement';
$isEdit = ($equipement !== null);
require APP_ROOT . '/views/layout/admin_header.php';
?>

<div class="panel">
    <h2><?= $isEdit ? 'Modifier l\'équipement #' . (int) $equipement['id'] : 'Ajouter un équipement' ?></h2>
</div>

<!-- ============================================================
     MAIN CRUD FORM — standard POST, no file upload
     action → POST /admin/equipement/save
     ============================================================ -->
<form method="post"
      action="<?= e(url('admin/equipement/save')) ?>"
      class="form panel"
      id="equip-form">

    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) ($equipement['id'] ?? 0) ?>">

    <!-- Nom -->
    <label>
        Nom <span style="color:var(--bad)">*</span>
        <input type="text"
               name="nom"
               value="<?= e($equipement['nom'] ?? '') ?>"
               required
               maxlength="150"
               placeholder="Ex : Perceuse percussion 18V">
    </label>

    <!-- Description -->
    <label>
        Description
        <textarea name="description"
                  rows="4"
                  placeholder="Caractéristiques, accessoires inclus…"><?= e($equipement['description'] ?? '') ?></textarea>
    </label>

    <!-- Prix / Stock / Seuil -->
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

    <!-- État + Catégorie -->
    <div class="form-row">
        <label>
            État <span style="color:var(--bad)">*</span>
            <select name="etat" required>
                <?php foreach (EQUIPEMENT_ETATS as $opt): ?>
                    <option value="<?= e($opt) ?>"
                        <?= (($equipement['etat'] ?? 'disponible') === $opt) ? 'selected' : '' ?>>
                        <?= e($opt) ?>
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

    <!-- Boutons -->
    <div style="display:flex;gap:.75rem;align-items:center;margin-top:.5rem">
        <button class="btn btn-primary" type="submit">
            <?= $isEdit ? '💾 Enregistrer les modifications' : '➕ Ajouter l\'équipement' ?>
        </button>
        <a class="btn btn-ghost" href="<?= e(url('admin/equipements')) ?>">Annuler</a>
    </div>

</form>

<!-- ============================================================
     PHOTO UPLOAD — separate multipart form, visible only on edit
     ============================================================ -->
<?php if ($isEdit): ?>
<form method="post"
      action="<?= e(url('admin/equipement/save')) ?>"
      class="form panel"
      enctype="multipart/form-data"
      style="margin-top:1rem">

    <?= csrf_field() ?>
    <!-- Keep the same id so save() knows it's an update -->
    <input type="hidden" name="id" value="<?= (int) $equipement['id'] ?>">
    <!-- Re-send text fields so the update doesn't blank them out -->
    <input type="hidden" name="nom"            value="<?= e($equipement['nom']            ?? '') ?>">
    <input type="hidden" name="description"    value="<?= e($equipement['description']    ?? '') ?>">
    <input type="hidden" name="prix_jour"      value="<?= e($equipement['prix_jour']      ?? '') ?>">
    <input type="hidden" name="quantite_stock" value="<?= (int) ($equipement['quantite_stock'] ?? 0) ?>">
    <input type="hidden" name="seuil_alerte"   value="<?= (int) ($equipement['seuil_alerte']   ?? 1) ?>">
    <input type="hidden" name="etat"           value="<?= e($equipement['etat']           ?? 'disponible') ?>">
    <input type="hidden" name="categorie_id"   value="<?= (int) ($equipement['categorie_id']   ?? 0) ?>">

    <fieldset style="border:1px solid var(--line);border-radius:var(--radius);padding:1rem 1.2rem">
        <legend style="font-weight:600;padding:0 .4rem">Photo</legend>

        <?php if (!empty($equipement['photo'])): ?>
            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:.8rem;flex-wrap:wrap">
                <img src="<?= e(url($equipement['photo'])) ?>"
                     alt="Photo actuelle"
                     style="width:120px;height:90px;object-fit:cover;border-radius:6px;border:1px solid var(--line)">
                <label style="display:flex;align-items:center;gap:.4rem;font-size:.9rem;color:var(--bad);cursor:pointer">
                    <input type="checkbox" name="remove_photo" value="1">
                    Supprimer la photo actuelle
                </label>
            </div>
        <?php endif; ?>

        <label style="display:inline-block;cursor:pointer;font-weight:600">
            <?= !empty($equipement['photo']) ? 'Remplacer l\'image' : 'Ajouter une image' ?>
            <input type="file"
                   name="photo"
                   accept="image/jpeg,image/png,image/webp,image/gif"
                   style="display:block;margin-top:.35rem"
                   id="photo-file">
        </label>
        <p class="hint">JPEG, PNG, WebP ou GIF · max 2 Mo</p>
        <img id="photo-preview" src="" alt="Aperçu"
             style="display:none;width:120px;height:90px;object-fit:cover;border-radius:6px;border:1px solid var(--line);margin-top:.5rem">
    </fieldset>

    <div style="margin-top:.75rem">
        <button class="btn btn-primary" type="submit">💾 Mettre à jour la photo</button>
    </div>

</form>

<script>
document.getElementById('photo-file')?.addEventListener('change', function () {
    const p = document.getElementById('photo-preview');
    if (this.files && this.files[0]) {
        p.src = URL.createObjectURL(this.files[0]);
        p.style.display = 'block';
    } else {
        p.style.display = 'none';
    }
});
</script>
<?php endif; ?>

<?php require APP_ROOT . '/views/layout/admin_footer.php'; ?>
