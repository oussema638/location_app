<?php
$title = 'Tableau de bord';
require APP_ROOT . '/views/layout/admin_header.php';
?>

<!-- ── KPI strip ─────────────────────────────────────────────────── -->
<div class="stats">
    <article class="stat">
        <span>Équipements</span>
        <strong><?= (int) $nbEquipements ?></strong>
    </article>
    <article class="stat">
        <span>Utilisateurs</span>
        <strong><?= (int) $nbUsers ?></strong>
    </article>
    <article class="stat">
        <span>CA confirmé&nbsp;+</span>
        <strong><?= number_format((float) $ca, 0, ',', ' ') ?>&nbsp;€</strong>
    </article>
    <article class="stat warn">
        <span>Alertes stock</span>
        <strong><?= count($alertes) ?></strong>
    </article>
</div>

<!-- ── Two-column panels ─────────────────────────────────────────── -->
<div class="admin-grid">

    <!-- Equipment état breakdown -->
    <section class="panel">
        <h2>États du parc</h2>
        <ul>
            <?php foreach (EQUIPEMENT_ETATS as $etat): ?>
                <li>
                    <?= etat_badge($etat) ?>
                    <strong><?= (int) ($etatMap[$etat] ?? 0) ?></strong>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <!-- Location statut breakdown -->
    <section class="panel">
        <h2>Locations par statut</h2>
        <ul>
            <?php foreach (LOCATION_STATUTS as $st): ?>
                <li>
                    <?= statut_badge($st) ?>
                    <strong><?= (int) ($statutMap[$st] ?? 0) ?></strong>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

</div>

<!-- ── Low-stock alert ───────────────────────────────────────────── -->
<section class="panel" style="margin-top:1.25rem">
    <h2>Stock sous le seuil d'alerte</h2>
    <?php if (!$alertes): ?>
        <p style="color:var(--muted-light);font-style:italic">Aucune alerte.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($alertes as $a): ?>
                <li>
                    <span><?= e($a['nom']) ?></span>
                    <span style="color:var(--warn);font-weight:700">
                        stock&nbsp;<?= (int) $a['quantite_stock'] ?>&nbsp;/&nbsp;seuil&nbsp;<?= (int) $a['seuil_alerte'] ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<!-- ── Recent rentals ────────────────────────────────────────────── -->
<section class="panel" style="margin-top:1.25rem">
    <h2>Dernières locations</h2>
    <?php if (!$locations): ?>
        <p style="color:var(--muted-light);font-style:italic">Aucune location enregistrée.</p>
    <?php else: ?>
    <div class="table-wrap" style="border:none;box-shadow:none">
        <table>
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Équipement</th>
                    <th>Qté</th>
                    <th>Dates</th>
                    <th>Montant</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($locations as $row): ?>
                <tr>
                    <td><?= e($row['utilisateur_prenom'] . ' ' . $row['utilisateur_nom']) ?></td>
                    <td><?= e($row['equipement_nom']) ?></td>
                    <td style="text-align:center;font-weight:700"><?= (int) $row['quantite'] ?></td>
                    <td style="white-space:nowrap;font-size:.82rem">
                        <?= e($row['date_debut']) ?> → <?= e($row['date_fin']) ?>
                    </td>
                    <td style="color:var(--orange);font-weight:700">
                        <?= number_format((float) $row['montant_total'], 2, ',', ' ') ?> €
                    </td>
                    <td><?= statut_badge($row['statut']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>

<?php require APP_ROOT . '/views/layout/admin_footer.php'; ?>
