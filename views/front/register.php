<?php
$title = 'Inscription';
require APP_ROOT . '/views/layout/header.php';
?>
<section class="section">
    <div class="container auth-box">
        <h1>Créer un compte</h1>
        <form method="post" action="<?= e(url('register')) ?>" class="form">
            <?= csrf_field() ?>
            <div class="form-row">
                <label>
                    Nom
                    <input type="text" name="nom" value="<?= old('nom') ?>">
                </label>
                <label>
                    Prénom
                    <input type="text" name="prenom" value="<?= old('prenom') ?>">
                </label>
            </div>
            <label>
                Email
                <input type="text" name="email" value="<?= old('email') ?>">
            </label>
            <label>
                Mot de passe
                <input type="password" name="password">
            </label>
            <button class="btn btn-primary" type="submit">S'inscrire</button>
        </form>
        <p>Déjà inscrit ? <a href="<?= e(url('login')) ?>">Connexion</a></p>
    </div>
</section>
<?php require APP_ROOT . '/views/layout/footer.php'; ?>
