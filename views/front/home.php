<?php
$title = 'Accueil';
require APP_ROOT . '/views/layout/header.php';
?>
<section class="hero">
    <div class="container hero-grid">
        <div>
            <p class="eyebrow">Location professionnelle</p>
            <h1>Matériel fiable, disponible à la journée.</h1>
            <p class="lead">Réservez outillage, machines et équipements. Suivi du stock, alertes et gestion des contrats dans un même espace.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="<?= e(url('equipements')) ?>">Voir le catalogue</a>
                <?php if (!is_logged_in()): ?>
                    <a class="btn btn-ghost" href="<?= e(url('register')) ?>">Créer un compte client</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero-panel">
            <h2>Catégories</h2>
            <ul class="category-list">
                <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="<?= e(url('equipements?categorie=' . $cat['id'])) ?>">
                            <strong><?= e($cat['nom']) ?></strong>
                            <span><?= (int) $cat['nb_equipements'] ?> équipement(s)</span>
                        </a>
                    </li>
                <?php endforeach; ?>
                <?php if (!$categories): ?>
                    <li>Aucune catégorie pour le moment.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Disponibles maintenant</h2>
            <a href="<?= e(url('equipements')) ?>">Tout le catalogue</a>
        </div>
        <div class="card-grid">
            <?php foreach ($equipements as $item): ?>
                <article class="card">
                    <p class="tag"><?= e($item['categorie_nom']) ?></p>
                    <h3><?= e($item['nom']) ?></h3>
                    <p><?= e(excerpt($item['description'] ?? '', 110)) ?></p>
                    <p class="price"><?= number_format((float) $item['prix_jour'], 2, ',', ' ') ?> € <span>/ jour</span></p>
                    <a class="btn btn-primary" href="<?= e(url('equipements/show/' . $item['id'])) ?>">Détail</a>
                </article>
            <?php endforeach; ?>
            <?php if (!$equipements): ?>
                <p>Aucun équipement disponible.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require APP_ROOT . '/views/layout/footer.php'; ?>
