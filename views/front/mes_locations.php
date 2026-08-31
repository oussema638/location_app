<?php
$title = 'Mes locations';
require APP_ROOT . '/views/layout/header.php';
?>
<section class="section">
    <div class="container">
        <h1>Mes locations</h1>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Équipement</th>
                    <th>Période</th>
                    <th>Statut</th>
                    <th>Montant</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($locations as $row): ?>
                    <tr>
                        <td><?= e($row['equipement_nom']) ?></td>
                        <td><?= e($row['date_debut']) ?> → <?= e($row['date_fin']) ?></td>
                        <td><span class="badge statut-<?= e(str_replace(' ', '-', $row['statut'])) ?>"><?= e($row['statut']) ?></span></td>
                        <td><?= number_format((float) $row['montant_total'], 2, ',', ' ') ?> €</td>
                        <td class="actions-cell">
                            <a class="btn btn-ghost"
                               href="<?= e(url('locations/pdf/' . (int) $row['id'])) ?>"
                               target="_blank"
                               title="Télécharger le contrat PDF">
                                📄 PDF
                            </a>
                            <?php if (in_array($row['statut'], ['en attente', 'confirmée'], true)): ?>
                                <form method="post" action="<?= e(url('locations/cancel')) ?>" onsubmit="return confirm('Annuler cette location ?');" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                    <button class="btn btn-ghost" type="submit">Annuler</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$locations): ?>
                    <tr><td colspan="5">Aucune location. <a href="<?= e(url('equipements')) ?>">Parcourir le catalogue</a></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require APP_ROOT . '/views/layout/footer.php'; ?>
