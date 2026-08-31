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
    <title><?= e($title ?? 'Back-office') ?> — LOCATECH</title>
    <link rel="stylesheet" href="<?= e(url('css/style.css')) ?>">
</head>
<body class="back-office">
<div class="admin-shell">
    <aside class="sidebar">
        <a class="brand" href="<?= e(url('admin')) ?>">
            <span class="brand-mark" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
            </span>
            <span class="brand-name">LOCA<strong>TECH</strong></span>
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
