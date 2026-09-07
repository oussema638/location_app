<?php
$title = 'Locations';
require APP_ROOT . '/views/layout/admin_header.php';
?>

<!-- Filter bar -->
<form class="filters" method="get" action="<?= e(url('admin/locations')) ?>">
    <label>
        Statut
        <select name="statut">
            <option value="">Tous les statuts</option>
            <?php foreach (LOCATION_STATUTS as $st): ?>
                <option value="<?= e($st) ?>"
                    <?= ($statut ?? '') === $st ? 'selected' : '' ?>>
                    <?= e(statut_label($st)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <button class="btn btn-primary" type="submit">Filtrer</button>
    <?php if (!empty($statut)): ?>
        <a class="btn btn-ghost" href="<?= e(url('admin/locations')) ?>">Réinitialiser</a>
    <?php endif; ?>
</form>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Client</th>
                <th>Équipement</th>
                <th>Qté</th>
                <th>Période</th>
                <th>Montant</th>
                <th>Frais</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($locations as $row): ?>
            <tr>
                <td style="color:var(--muted-light);font-size:.8rem"><?= (int) $row['id'] ?></td>
                <td>
                    <?= e($row['utilisateur_prenom'] . ' ' . $row['utilisateur_nom']) ?>
                    <br><small style="color:var(--muted-light)"><?= e($row['utilisateur_email']) ?></small>
                </td>
                <td><?= e($row['equipement_nom']) ?></td>
                <td style="text-align:center;font-weight:700"><?= (int) $row['quantite'] ?></td>
                <td style="white-space:nowrap">
                    <?= e($row['date_debut']) ?><br>
                    <span style="color:var(--muted-light)">→ <?= e($row['date_fin']) ?></span>
                </td>
                <td style="font-weight:700;color:var(--orange)">
                    <?= number_format((float) $row['montant_total'], 2, ',', ' ') ?> €
                </td>
                <td><?= number_format((float) $row['frais_additionnels'], 2, ',', ' ') ?> €</td>
                <td><?= statut_badge($row['statut']) ?></td>
                <td>
                    <div class="actions">
                        <!-- PDF -->
                        <a class="btn btn-ghost"
                           href="<?= e(url('locations/pdf/' . (int) $row['id'])) ?>"
                           target="_blank" title="Contrat PDF"
                           style="padding:.4rem .6rem">📄</a>

                        <!-- Status + frais update -->
                        <form method="post"
                              action="<?= e(url('admin/locations/update')) ?>"
                              class="inline-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">

                            <select name="statut" style="padding:.35rem .5rem;font-size:.82rem">
                                <?php foreach (LOCATION_STATUTS as $st): ?>
                                    <option value="<?= e($st) ?>"
                                        <?= $row['statut'] === $st ? 'selected' : '' ?>>
                                        <?= e(statut_label($st)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <input type="number" step="0.01" min="0"
                                   name="frais_additionnels"
                                   value="<?= e($row['frais_additionnels']) ?>"
                                   title="Frais additionnels (€)"
                                   placeholder="0.00"
                                   style="width:80px;padding:.35rem .5rem;font-size:.82rem">

                            <button class="btn btn-primary" type="submit"
                                    style="padding:.4rem .75rem">OK</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$locations): ?>
            <tr><td colspan="9" class="empty-row">Aucune location trouvée.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require APP_ROOT . '/views/layout/admin_footer.php'; ?>
