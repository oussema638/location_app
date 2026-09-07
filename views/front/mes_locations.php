<?php
$title = 'Mes locations';
require APP_ROOT . '/views/layout/header.php';
?>
<section class="section">
    <div class="container">

        <h1 style="margin-bottom:1.25rem">Mes locations</h1>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Équipement</th>
                        <th>Qté</th>
                        <th>Période</th>
                        <th>Statut</th>
                        <th>Montant</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($locations as $row): ?>
                    <tr>
                        <td style="font-weight:600"><?= e($row['equipement_nom']) ?></td>

                        <td style="text-align:center;font-weight:700">
                            <?= (int) $row['quantite'] ?>
                        </td>

                        <td style="white-space:nowrap;font-size:.875rem">
                            <?= e($row['date_debut']) ?>
                            <span style="color:var(--muted-light)"> → </span>
                            <?= e($row['date_fin']) ?>
                        </td>

                        <td><?= statut_badge($row['statut']) ?></td>

                        <td style="color:var(--orange);font-weight:700">
                            <?= number_format((float) $row['montant_total'], 2, ',', ' ') ?> €
                        </td>

                        <td class="actions-cell">
                            <!-- PDF download -->
                            <a class="btn btn-ghost"
                               href="<?= e(url('locations/pdf/' . (int) $row['id'])) ?>"
                               target="_blank"
                               title="Télécharger le contrat PDF"
                               style="padding:.4rem .65rem">
                                📄
                            </a>

                            <!-- Cancel (only if still cancellable) -->
                            <?php if (in_array($row['statut'], ['en attente', 'confirmee'], true)): ?>
                                <form method="post"
                                      action="<?= e(url('locations/cancel')) ?>"
                                      onsubmit="return confirm('Annuler cette location ?');"
                                      style="display:inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                    <button class="btn btn-ghost btn-danger" type="submit">
                                        Annuler
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$locations): ?>
                    <tr>
                        <td colspan="6" class="empty-row">
                            Aucune location.
                            <a href="<?= e(url('equipements')) ?>">Parcourir le catalogue →</a>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</section>
<?php require APP_ROOT . '/views/layout/footer.php'; ?>
