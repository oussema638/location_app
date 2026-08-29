<?php
$title = 'Équipements';
require APP_ROOT . '/views/layout/admin_header.php';
?>
<div class="toolbar">
    <a class="btn btn-primary" href="<?= e(url('admin/equipements/create')) ?>">Ajouter</a>
</div>
<?php if ($alertes): ?>
    <div class="alert alert-error">
        <?= count($alertes) ?> équipement(s) sous le seuil d'alerte.
    </div>
<?php endif; ?>
<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Nom</th>
            <th>Catégorie</th>
            <th>Prix/jour</th>
            <th>Stock</th>
            <th>Seuil</th>
            <th>État</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($equipements as $item): ?>
            <tr class="<?= (int) $item['quantite_stock'] <= (int) $item['seuil_alerte'] ? 'row-alert' : '' ?>">
                <td><?= e($item['nom']) ?></td>
                <td><?= e($item['categorie_nom']) ?></td>
                <td><?= number_format((float) $item['prix_jour'], 2, ',', ' ') ?> €</td>
                <td><?= (int) $item['quantite_stock'] ?></td>
                <td><?= (int) $item['seuil_alerte'] ?></td>
                <td><?= e($item['etat']) ?></td>
                <td class="actions">
                    <a class="btn btn-ghost" href="<?= e(url('admin/equipements/edit/' . $item['id'])) ?>">Modifier</a>
                    <form method="post" action="<?= e(url('admin/equipements/delete')) ?>" onsubmit="return confirm('Supprimer cet équipement ?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                        <button class="btn btn-ghost" type="submit">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$equipements): ?>
            <tr><td colspan="7">Aucun équipement.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require APP_ROOT . '/views/layout/admin_footer.php'; ?>
