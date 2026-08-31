<?php
$title = 'Équipements';
require APP_ROOT . '/views/layout/admin_header.php';
?>
<div class="toolbar">
    <a class="btn btn-primary" href="<?= e(url('admin/equipement/add')) ?>">➕ Ajouter un équipement</a>
</div>

<?php if ($alertes): ?>
    <div class="alert alert-error">
        ⚠️ <?= count($alertes) ?> équipement<?= count($alertes) > 1 ? 's' : '' ?> sous le seuil d'alerte :
        <?= implode(', ', array_map(fn($a) => e($a['nom']), $alertes)) ?>.
    </div>
<?php endif; ?>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th style="width:64px">Photo</th>
                <th>Nom</th>
                <th>Catégorie</th>
                <th>Prix/jour</th>
                <th>Stock</th>
                <th>Seuil</th>
                <th>État</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($equipements as $item): ?>
            <?php $lowStock = (int) $item['quantite_stock'] <= (int) $item['seuil_alerte']; ?>
            <tr class="<?= $lowStock ? 'row-alert' : '' ?>">
                <td>
                    <?php if (!empty($item['photo'])): ?>
                        <img src="<?= e(url($item['photo'])) ?>"
                             alt="<?= e($item['nom']) ?>"
                             class="admin-thumb"
                             loading="lazy">
                    <?php else: ?>
                        <span class="no-photo" title="Pas de photo">—</span>
                    <?php endif; ?>
                </td>
                <td><?= e($item['nom']) ?></td>
                <td><?= e($item['categorie_nom']) ?></td>
                <td><?= number_format((float) $item['prix_jour'], 2, ',', ' ') ?> €</td>
                <td class="<?= $lowStock ? 'text-warn' : '' ?>"><?= (int) $item['quantite_stock'] ?></td>
                <td><?= (int) $item['seuil_alerte'] ?></td>
                <td>
                    <span class="badge etat-<?= e(str_replace(' ', '-', $item['etat'])) ?>">
                        <?= e($item['etat']) ?>
                    </span>
                </td>
                <td class="actions">
                    <a class="btn btn-ghost"
                       href="<?= e(url('admin/equipement/edit/' . (int) $item['id'])) ?>">
                        ✏️ Modifier
                    </a>
                    <form method="post"
                          action="<?= e(url('admin/equipement/delete')) ?>"
                          onsubmit="return confirm('Supprimer « <?= e(addslashes($item['nom'])) ?> » ? Cette action est irréversible.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                        <button class="btn btn-ghost btn-danger" type="submit">🗑 Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$equipements): ?>
            <tr><td colspan="8" class="empty-row">Aucun équipement enregistré.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
    .admin-thumb {
        width: 56px; height: 42px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid var(--line);
        display: block;
    }
    .no-photo { color: var(--muted); font-size: 1.1rem; }
    .text-warn { color: var(--bad); font-weight: 700; }
    .empty-row { text-align: center; color: var(--muted); padding: 2rem; }
    .btn-danger { color: var(--bad); border-color: var(--bad); }
    .btn-danger:hover { background: var(--bad); color: white; }
    /* État badges */
    .etat-disponible      { background: #d1fae5; color: #065f46; }
    .etat-en-location     { background: #dbeafe; color: #1e40af; }
    .etat-en-maintenance  { background: #fef3c7; color: #92400e; }
    .etat-endommag\E9     { background: #fee2e2; color: #991b1b; }
</style>

<?php require APP_ROOT . '/views/layout/admin_footer.php'; ?>
