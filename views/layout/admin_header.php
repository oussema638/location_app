<?php
$user = current_user();
$success = flash('success');
$error = flash('error');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Back-office') ?> — location_app</title>
    <link rel="stylesheet" href="<?= e(url('css/style.css')) ?>">
</head>
<body class="back-office">
<div class="admin-shell">
    <aside class="sidebar">
        <a class="brand" href="<?= e(url('admin')) ?>">
            <span class="brand-mark">LA</span>
            <span>Back-office</span>
        </a>
        <nav>
            <a href="<?= e(url('admin')) ?>">Tableau de bord</a>
            <a href="<?= e(url('admin/equipements')) ?>">Équipements</a>
            <a href="<?= e(url('admin/categories')) ?>">Catégories</a>
            <a href="<?= e(url('admin/locations')) ?>">Locations</a>
            <?php if (has_role('responsable')): ?>
                <a href="<?= e(url('admin/utilisateurs')) ?>">Utilisateurs</a>
            <?php endif; ?>
            <a href="<?= e(url()) ?>">Site public</a>
        </nav>
        <p class="sidebar-user"><?= e($user['prenom'] . ' ' . $user['nom']) ?><br><small><?= e($user['role']) ?></small></p>
    </aside>
    <div class="admin-main">
        <header class="admin-top">
            <h1><?= e($title ?? 'Administration') ?></h1>
            <a class="btn btn-ghost" href="<?= e(url('logout')) ?>">Déconnexion</a>
        </header>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
