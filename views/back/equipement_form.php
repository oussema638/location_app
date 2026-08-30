<?php
$title = $equipement ? 'Modifier un équipement' : 'Nouvel équipement';
require APP_ROOT . '/views/layout/admin_header.php';
?>
<form method="post" action="<?= e(url('admin/equipements/save')) ?>" class="form panel">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) ($equipement['id'] ?? 0) ?>">
    <label>
        Nom
        <input type="text" name="nom" value="<?= e($equipement['nom'] ?? '') ?>">
    </label>
    <label>
        Description
        <textarea name="description" rows="4"><?= e($equipement['description'] ?? '') ?></textarea>
    </label>
    <div class="form-row">
        <label>
            Prix / jour (€)
            <input type="number" name="prix_jour" value="<?= e($equipement['prix_jour'] ?? '') ?>">
        </label>
        <label>
            Quantité en stock
            <input type="number" name="quantite_stock" value="<?= e($equipement['quantite_stock'] ?? '0') ?>">
        </label>
        <label>
            Seuil d'alerte
            <input type="number" name="seuil_alerte" value="<?= e($equipement['seuil_alerte'] ?? '1') ?>">
        </label>
    </div>
    <div class="form-row">
        <label>
            État
            <select name="etat">
                <?php foreach (EQUIPEMENT_ETATS as $etat): ?>
                    <option value="<?= e($etat) ?>" <?= (($equipement['etat'] ?? '') === $etat) ? 'selected' : '' ?>><?= e($etat) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Catégorie
            <select name="categorie_id">
                <option value="">Choisir</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>" <?= ((int) ($equipement['categorie_id'] ?? 0) === (int) $cat['id']) ? 'selected' : '' ?>>
                        <?= e($cat['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>
    <button class="btn btn-primary" type="submit">Enregistrer</button>
    <a class="btn btn-ghost" href="<?= e(url('admin/equipements')) ?>">Annuler</a>
</form>
<?php require APP_ROOT . '/views/layout/admin_footer.php'; ?>
