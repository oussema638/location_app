<?php
$title = $equipement['nom'];
require APP_ROOT . '/views/layout/header.php';
$stock = (int) $equipement['quantite_stock'];
?>
<section class="section">
    <div class="container detail">

        <p style="margin-bottom:1rem">
            <a href="<?= e(url('equipements')) ?>">&larr; Catalogue</a>
        </p>

        <p class="tag"><?= e($equipement['categorie_nom']) ?></p>
        <h1 style="margin:.5rem 0 .75rem"><?= e($equipement['nom']) ?></h1>
        <p class="lead"><?= nl2br(e($equipement['description'] ?? '')) ?></p>

        <ul class="specs" style="margin:1.25rem 0">
            <li><strong>Prix / jour</strong> <?= number_format((float) $equipement['prix_jour'], 2, ',', ' ') ?> €</li>
            <li>
                <strong>Stock disponible</strong>
                <span style="color:<?= $stock > 0 ? 'var(--ok)' : 'var(--bad)' ?>">
                    <?= $stock ?> unité<?= $stock !== 1 ? 's' : '' ?>
                </span>
            </li>
            <li>
                <strong>État</strong>
                <span class="badge etat-<?= e(str_replace(' ', '-', $equipement['etat'])) ?>">
                    <?= e($equipement['etat']) ?>
                </span>
            </li>
        </ul>

        <?php if ($equipement['etat'] === 'disponible' && $stock > 0): ?>
            <?php if (is_logged_in()): ?>
                <a class="btn btn-primary"
                   href="<?= e(url('locations/create/' . (int) $equipement['id'])) ?>">
                    Louer cet équipement
                </a>
            <?php else: ?>
                <a class="btn btn-primary" href="<?= e(url('login')) ?>">
                    Connectez-vous pour louer
                </a>
            <?php endif; ?>
        <?php else: ?>
            <p class="alert alert-error" style="display:inline-block">
                Indisponible à la location pour le moment.
            </p>
        <?php endif; ?>

    </div>
</section>
<?php require APP_ROOT . '/views/layout/footer.php'; ?>
