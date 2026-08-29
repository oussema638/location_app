<?php
$title = 'Tableau de bord';
require APP_ROOT . '/views/layout/admin_header.php';

$etatMap = [];
foreach ($etats as $row) {
    $etatMap[$row['etat']] = (int) $row['total'];
}
$statutMap = [];
foreach ($statuts as $row) {
    $statutMap[$row['statut']] = (int) $row['total'];
}
?>
<div class="stats">
    <article class="stat"><span>Équipements</span><strong><?= (int) $nbEquipements ?></strong></article>
    <article class="stat"><span>Utilisateurs</span><strong><?= (int) $nbUsers ?></strong></article>
    <article class="stat"><span>CA (confirmé+)</strong><strong><?= number_format((float) $ca, 2, ',', ' ') ?> €</strong></article>
    <article class="stat warn"><span>Alertes stock</span><strong><?= count($alertes) ?></strong></article>
</div>
<div class="admin-grid">
    <section class="panel">
        <h2>États du parc</h2>
        <ul>
            <?php foreach (EQUIPEMENT_ETATS as $etat): ?>
                <li><?= e($etat) ?> — <?= (int) ($etatMap[$etat] ?? 0) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
    <section class="panel">
        <h2>Locations par statut</h2>
        <ul>
            <?php foreach (LOCATION_STATUTS as $st): ?>
                <li><?= e($st) ?> — <?= (int) ($statutMap[$st] ?? 0) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>
<section class="panel">
    <h2>Stock sous le seuil d'alerte</h2>
    <?php if (!$alertes): ?>
        <p>Aucune alerte.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($alertes as $a): ?>
                <li><?= e($a['nom']) ?> — stock <?= (int) $a['quantite_stock'] ?> / seuil <?= (int) $a['seuil_alerte'] ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
<section class="panel">
    <h2>Dernières locations</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr><th>Client</th><th>Équipement</th><th>Dates</th><th>Statut</th></tr>
            </thead>
            <tbody>
            <?php foreach ($locations as $row): ?>
                <tr>
                    <td><?= e($row['utilisateur_prenom'] . ' ' . $row['utilisateur_nom']) ?></td>
                    <td><?= e($row['equipement_nom']) ?></td>
                    <td><?= e($row['date_debut']) ?> → <?= e($row['date_fin']) ?></td>
                    <td><?= e($row['statut']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require APP_ROOT . '/views/layout/admin_footer.php'; ?>
