<?php
$title = 'Réserver';
require APP_ROOT . '/views/layout/header.php';
?>
<section class="section">
    <div class="container auth-box">
        <h1>Louer <?= e($equipement['nom']) ?></h1>
        <p><?= number_format((float) $equipement['prix_jour'], 2, ',', ' ') ?> € / jour</p>
        <form method="post" action="<?= e(url('locations/store')) ?>" class="form" id="location-form">
            <?= csrf_field() ?>
            <input type="hidden" name="equipement_id" value="<?= (int) $equipement['id'] ?>">
            <div class="form-row">
                <label>
                    Date de début
                    <input type="date" name="date_debut">
                </label>
                <label>
                    Date de fin
                    <input type="date" name="date_fin">
                </label>
            </div>
            <p id="montant-estime" class="hint">Sélectionnez les dates pour estimer le montant.</p>
            <button class="btn btn-primary" type="submit">Envoyer la demande</button>
        </form>
    </div>
</section>
<script>
    window.LOCATION_PRIX_JOUR = <?= json_encode((float) $equipement['prix_jour']) ?>;
</script>
<?php require APP_ROOT . '/views/layout/footer.php'; ?>
