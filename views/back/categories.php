<?php
$title = 'Catégories';
require APP_ROOT . '/views/layout/admin_header.php';
?>
<div class="admin-grid">
    <form method="post" action="<?= e(url('admin/categories')) ?>" class="form panel">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
        <h2><?= $edit ? 'Modifier' : 'Nouvelle catégorie' ?></h2>
        <label>
            Nom
            <input type="text" name="nom" value="<?= e($edit['nom'] ?? '') ?>">
        </label>
        <label>
            Description
            <textarea name="description" rows="3"><?= e($edit['description'] ?? '') ?></textarea>
        </label>
        <button class="btn btn-primary" type="submit">Enregistrer</button>
        <?php if ($edit): ?>
            <a class="btn btn-ghost" href="<?= e(url('admin/categories')) ?>">Annuler</a>
        <?php endif; ?>
    </form>
    <div class="panel">
        <h2>Liste</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>Nom</th><th>Équipements</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?= e($cat['nom']) ?></td>
                        <td><?= (int) $cat['nb_equipements'] ?></td>
                        <td class="actions">
                            <a class="btn btn-ghost" href="<?= e(url('admin/categories?edit=' . $cat['id'])) ?>">Modifier</a>
                            <form method="post" action="<?= e(url('admin/categories')) ?>" onsubmit="return confirm('Supprimer cette catégorie ?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
                                <button class="btn btn-ghost" type="submit">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require APP_ROOT . '/views/layout/admin_footer.php'; ?>
