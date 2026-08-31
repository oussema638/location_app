<?php
$title = 'Catalogue';
require APP_ROOT . '/views/layout/header.php';
?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <h1>Catalogue d'équipements</h1>
        </div>

        <!-- Filter form — GET so params land in $_GET, action goes to the catalogue route -->
        <form class="filters" method="get" action="<?= e(url('equipements')) ?>">
            <label>
                Catégorie
                <select name="categorie">
                    <option value="">Toutes les catégories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>"
                                <?= ($categorieId === (int) $cat['id']) ? 'selected' : '' ?>>
                            <?= e($cat['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                État
                <select name="etat">
                    <option value="disponible" <?= ($etat === 'disponible') ? 'selected' : '' ?>>Disponible</option>
                    <option value="all"        <?= ($etat === 'all')        ? 'selected' : '' ?>>Tous les états</option>
                    <?php foreach (EQUIPEMENT_ETATS as $st): ?>
                        <?php if ($st === 'disponible') continue; ?>
                        <option value="<?= e($st) ?>" <?= ($etat === $st) ? 'selected' : '' ?>>
                            <?= e($st) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <button class="btn btn-primary" type="submit">Filtrer</button>

            <?php if ($isFiltered): ?>
                <a class="btn btn-ghost" href="<?= e(url('equipements')) ?>">Réinitialiser</a>
            <?php endif; ?>
        </form>

        <?php if ($isFiltered): ?>
            <p class="filter-hint">
                <?= (int) $totalCount ?> résultat<?= $totalCount !== 1 ? 's' : '' ?>
            </p>
        <?php endif; ?>

        <div class="card-grid">
            <?php foreach ($equipements as $item): ?>
                <article class="card">
                    <?php if (!empty($item['photo'])): ?>
                        <div class="card-img-wrap">
                            <img src="<?= e(url($item['photo'])) ?>"
                                 alt="<?= e($item['nom']) ?>"
                                 class="card-img"
                                 loading="lazy">
                        </div>
                    <?php else: ?>
                        <div class="card-img-wrap card-img-placeholder" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                        </div>
                    <?php endif; ?>

                    <div class="card-body">
                        <p class="tag"><?= e($item['categorie_nom']) ?></p>
                        <h3><?= e($item['nom']) ?></h3>
                        <p class="card-desc"><?= e(excerpt($item['description'] ?? '', 120)) ?></p>
                        <p class="meta">
                            Stock&nbsp;: <?= (int) $item['quantite_stock'] ?>
                            &middot; <?= e($item['etat']) ?>
                        </p>
                        <p class="price">
                            <?= number_format((float) $item['prix_jour'], 2, ',', ' ') ?>&nbsp;€
                            <span>/ jour</span>
                        </p>
                        <a class="btn btn-primary"
                           href="<?= e(url('equipements/show/' . (int) $item['id'])) ?>">
                            Voir le détail
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>

            <?php if (empty($equipements)): ?>
                <div class="empty-state">
                    <p>Aucun équipement ne correspond à ces filtres.</p>
                    <a class="btn btn-ghost" href="<?= e(url('equipements')) ?>">
                        Voir tout le catalogue
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>
<?php require APP_ROOT . '/views/layout/footer.php'; ?>
