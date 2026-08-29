<?php
$title = 'Utilisateurs';
require APP_ROOT . '/views/layout/admin_header.php';
?>
<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($utilisateurs as $u): ?>
            <tr>
                <td><?= e($u['prenom'] . ' ' . $u['nom']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td>
                    <form method="post" action="<?= e(url('admin/utilisateurs')) ?>" class="inline-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                        <select name="role">
                            <?php foreach (ROLES as $role): ?>
                                <option value="<?= e($role) ?>" <?= $u['role'] === $role ? 'selected' : '' ?>><?= e($role) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-primary" type="submit">OK</button>
                    </form>
                </td>
                <td>
                    <?php if ((int) $u['id'] !== (int) current_user()['id']): ?>
                        <form method="post" action="<?= e(url('admin/utilisateurs')) ?>" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                            <button class="btn btn-ghost" type="submit">Supprimer</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require APP_ROOT . '/views/layout/admin_footer.php'; ?>
