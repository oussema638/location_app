<?php
$title = 'Équipements';
require APP_ROOT . '/views/layout/admin_header.php';
?>
<div class="toolbar">
    <a class="btn btn-primary" href="<?= e(url('admin/equipement/add')) ?>">➕ Ajouter un équipement</a>
</div>

<?php if ($alertes): ?>
    <div class="alert alert-error">
        ⚠️ <?= count($alertes) ?> équipement<?= count($alertes) > 1 ? 's' : '' ?> sous le seuil d'alerte :
        <?= implode(', ', array_map(fn($a) => '<strong>' . e($a['nom']) . '</strong>', $alertes)) ?>.
    </div>
<?php endif; ?>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th style="width:60px">Photo</th>
                <th>Nom</th>
                <th>Catégorie</th>
                <th>Prix/jour</th>
                <th>Stock</th>
                <th>Seuil</th>
                <th>État</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($equipements as $item): ?>
            <?php $lowStock = (int) $item['quantite_stock'] <= (int) $item['seuil_alerte']; ?>
            <tr class="<?= $lowStock ? 'row-alert' : '' ?>">

                <td>
                    <?php if (!empty($item['photo'])): ?>
                        <img src="<?= e(url($item['photo'])) ?>"
                             alt="<?= e($item['nom']) ?>"
                             class="admin-thumb"
                             loading="lazy">
                    <?php else: ?>
                        <span class="no-photo">—</span>
                    <?php endif; ?>
                </td>

                <td style="font-weight:600"><?= e($item['nom']) ?></td>
                <td><?= e($item['categorie_nom']) ?></td>
                <td style="color:var(--orange);font-weight:700">
                    <?= number_format((float) $item['prix_jour'], 2, ',', ' ') ?> €
                </td>
                <td class="<?= $lowStock ? 'text-warn' : '' ?>" style="font-weight:700">
                    <?= (int) $item['quantite_stock'] ?>
                </td>
                <td style="color:var(--muted-light)"><?= (int) $item['seuil_alerte'] ?></td>

                <td><?= etat_badge($item['etat']) ?></td>

                <td class="actions">
                    <a class="btn btn-ghost"
                       href="<?= e(url('admin/equipement/edit/' . (int) $item['id'])) ?>">
                        ✏️ Modifier
                    </a>
                    <form method="post"
                          action="<?= e(url('admin/equipement/delete')) ?>"
                          onsubmit="return confirm('Supprimer « <?= e(addslashes($item['nom'])) ?> » ? Cette action est irréversible.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                        <button class="btn btn-ghost btn-danger" type="submit">🗑</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$equipements): ?>
            <tr><td colspan="8" class="empty-row">Aucun équipement enregistré.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require APP_ROOT . '/views/layout/admin_footer.php'; ?>
