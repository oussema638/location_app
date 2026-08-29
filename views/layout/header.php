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
    <title><?= e($title ?? 'Location d\'équipements') ?> — location_app</title>
    <link rel="stylesheet" href="<?= e(url('css/style.css')) ?>">
</head>
<body>
<header class="site-header">
    <div class="container nav">
        <a class="brand" href="<?= e(url()) ?>">
            <span class="brand-mark">LA</span>
            <span>location_app</span>
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
