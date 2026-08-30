<?php
$title = 'Connexion';
require APP_ROOT . '/views/layout/header.php';
?>
<section class="section">
    <div class="container auth-box">
        <h1>Connexion</h1>
        <form method="post" action="<?= e(url('login')) ?>" class="form">
            <?= csrf_field() ?>
            <label>
                Email
                <input type="text" name="email" value="<?= old('email') ?>">
            </label>
            <label>
                Mot de passe
                <input type="password" name="password">
            </label>
            <button class="btn btn-primary" type="submit">Se connecter</button>
        </form>
        <p>Pas encore de compte ? <a href="<?= e(url('register')) ?>">Inscription</a></p>
        <p class="hint">Compte démo responsable : responsable@location.local / Password123</p>
    </div>
</section>
<?php require APP_ROOT . '/views/layout/footer.php'; ?>
