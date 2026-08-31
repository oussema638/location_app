<?php
$title = 'Locations';
require APP_ROOT . '/views/layout/admin_header.php';
?>
<form class="filters" method="get" action="<?= e(url('admin/locations')) ?>">
    <label>
        Statut
        <select name="statut">
            <option value="">Tous</option>
            <?php foreach (LOCATION_STATUTS as $st): ?>
                <option value="<?= e($st) ?>" <?= $statut === $st ? 'selected' : '' ?>><?= e($st) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button class="btn btn-primary" type="submit">Filtrer</button>
</form>
<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Client</th>
            <th>Équipement</th>
            <th>Période</th>
            <th>Montant</th>
            <th>Frais</th>
            <th>Statut</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($locations as $row): ?>
            <tr>
                <td><?= e($row['utilisateur_prenom'] . ' ' . $row['utilisateur_nom']) ?><br><small><?= e($row['utilisateur_email']) ?></small></td>
                <td><?= e($row['equipement_nom']) ?></td>
                <td><?= e($row['date_debut']) ?> → <?= e($row['date_fin']) ?></td>
                <td><?= number_format((float) $row['montant_total'], 2, ',', ' ') ?> €</td>
                <td><?= number_format((float) $row['frais_additionnels'], 2, ',', ' ') ?> €</td>
                <td><?= e($row['statut']) ?></td>
                <td>
                    <a class="btn btn-ghost"
                       href="<?= e(url('locations/pdf/' . (int) $row['id'])) ?>"
                       target="_blank"
                       title="Contrat PDF">📄</a>
                    <form method="post" action="<?= e(url('admin/locations/update')) ?>" class="inline-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                        <select name="statut">
                            <?php foreach (LOCATION_STATUTS as $st): ?>
                                <option value="<?= e($st) ?>" <?= $row['statut'] === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" step="0.01" min="0" name="frais_additionnels" value="<?= e($row['frais_additionnels']) ?>" title="Frais additionnels">
                        <button class="btn btn-primary" type="submit">OK</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$locations): ?>
            <tr><td colspan="7">Aucune location.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require APP_ROOT . '/views/layout/admin_footer.php'; ?>
