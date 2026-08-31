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
    <title><?= e($title ?? 'Location d\'équipements') ?> — LOCATECH</title>
    <link rel="stylesheet" href="<?= e(url('css/style.css')) ?>">
</head>
<body>
<header class="site-header">
    <div class="container nav">
        <a class="brand" href="<?= e(url()) ?>">
            <span class="brand-mark" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
            </span>
            <span class="brand-name">LOCA<strong>TECH</strong></span>
        </a>
        <nav class="nav-links">
            <a href="<?= e(url()) ?>">Accueil</a>
            <a href="<?= e(url('equipements')) ?>">Catalogue</a>
            <?php if ($user): ?>
                <a href="<?= e(url('mes-locations')) ?>">Mes locations</a>
                <?php if (has_role('agent', 'responsable')): ?>
                    <a href="<?= e(url('admin')) ?>">Back-office</a>
                <?php endif; ?>
                <span class="nav-user"><?= e($user['prenom']) ?></span>
                <a class="btn btn-ghost" href="<?= e(url('logout')) ?>">Déconnexion</a>
            <?php else: ?>
                <a href="<?= e(url('login')) ?>">Connexion</a>
                <a class="btn btn-primary" href="<?= e(url('register')) ?>">Inscription</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main>
    <div class="container">
        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
    </div>
