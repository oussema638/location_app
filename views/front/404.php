<?php
$title = 'Page introuvable';
require APP_ROOT . '/views/layout/header.php';
?>
<section class="section">
    <div class="container">
        <h1>404</h1>
        <p>Cette page n'existe pas.</p>
        <a class="btn btn-primary" href="<?= e(url()) ?>">Retour à l'accueil</a>
    </div>
</section>
<?php require APP_ROOT . '/views/layout/footer.php'; ?>
