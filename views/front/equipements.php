<?php
$title = 'Catalogue';
require APP_ROOT . '/views/layout/header.php';
?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <h1>Catalogue d'équipements</h1>
        </div>
        <form class="filters" method="get" action="<?= e(url('equipements')) ?>">
            <label>
                Catégorie
                <select name="categorie">
                    <option value="">Toutes</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>" <?= (int) $categorieId === (int) $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                État
                <select name="etat">
                    <option value="disponible" <?= $etat === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                    <option value="all" <?= $etat === 'all' ? 'selected' : '' ?>>Tous</option>
                    <?php foreach (EQUIPEMENT_ETATS as $st): ?>
                        <?php if ($st === 'disponible') continue; ?>
                        <option value="<?= e($st) ?>" <?= $etat === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="btn btn-primary" type="submit">Filtrer</button>
        </form>
        <div class="card-grid">
            <?php foreach ($equipements as $item): ?>
                <article class="card">
                    <p class="tag"><?= e($item['categorie_nom']) ?></p>
                    <h3><?= e($item['nom']) ?></h3>
                    <p><?= e(excerpt($item['description'] ?? '', 120)) ?></p>
                    <p class="meta">Stock : <?= (int) $item['quantite_stock'] ?> · <?= e($item['etat']) ?></p>
                    <p class="price"><?= number_format((float) $item['prix_jour'], 2, ',', ' ') ?> € <span>/ jour</span></p>
                    <a class="btn btn-primary" href="<?= e(url('equipements/show/' . $item['id'])) ?>">Voir</a>
                </article>
            <?php endforeach; ?>
            <?php if (!$equipements): ?>
                <p>Aucun résultat pour ces filtres.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require APP_ROOT . '/views/layout/footer.php'; ?>
