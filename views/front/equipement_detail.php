<?php
$title = $equipement['nom'];
require APP_ROOT . '/views/layout/header.php';
?>
<section class="section">
    <div class="container detail">
        <p><a href="<?= e(url('equipements')) ?>">&larr; Catalogue</a></p>
        <p class="tag"><?= e($equipement['categorie_nom']) ?></p>
        <h1><?= e($equipement['nom']) ?></h1>
        <p class="lead"><?= nl2br(e($equipement['description'] ?? '')) ?></p>
        <ul class="specs">
            <li><strong>Prix / jour</strong> <?= number_format((float) $equipement['prix_jour'], 2, ',', ' ') ?> €</li>
            <li><strong>Stock</strong> <?= (int) $equipement['quantite_stock'] ?></li>
            <li><strong>État</strong> <?= e($equipement['etat']) ?></li>
        </ul>
        <?php if ($equipement['etat'] === 'disponible' && (int) $equipement['quantite_stock'] > 0): ?>
            <?php if (is_logged_in()): ?>
                <a class="btn btn-primary" href="<?= e(url('locations/create/' . $equipement['id'])) ?>">Louer cet équipement</a>
            <?php else: ?>
                <a class="btn btn-primary" href="<?= e(url('login')) ?>">Connectez-vous pour louer</a>
            <?php endif; ?>
        <?php else: ?>
            <p class="alert alert-error">Indisponible à la location pour le moment.</p>
        <?php endif; ?>
    </div>
</section>
<?php require APP_ROOT . '/views/layout/footer.php'; ?>
