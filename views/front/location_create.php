<?php
$title = 'Réserver — ' . e($equipement['nom']);
require APP_ROOT . '/views/layout/header.php';
$stock = (int) $equipement['quantite_stock'];
?>
<section class="section">
    <div class="container auth-box" style="max-width:520px">

        <h1 style="font-size:1.35rem;margin-bottom:.25rem">
            Louer : <?= e($equipement['nom']) ?>
        </h1>
        <p class="lead" style="margin-bottom:1.5rem">
            <?= number_format((float) $equipement['prix_jour'], 2, ',', ' ') ?>&nbsp;€&nbsp;/&nbsp;jour
            &nbsp;·&nbsp;
            <span style="color:var(--ok)">
                <?= $stock ?> unité<?= $stock > 1 ? 's' : '' ?> disponible<?= $stock > 1 ? 's' : '' ?>
            </span>
        </p>

        <form method="post"
              action="<?= e(url('locations/store')) ?>"
              class="form"
              id="location-form">

            <?= csrf_field() ?>
            <input type="hidden" name="equipement_id" value="<?= (int) $equipement['id'] ?>">

            <!-- Dates -->
            <div class="form-row">
                <label>
                    Date de début
                    <input type="date" name="date_debut" id="date_debut" required>
                </label>
                <label>
                    Date de fin
                    <input type="date" name="date_fin" id="date_fin" required>
                </label>
            </div>

            <!-- Quantity -->
            <label>
                Quantité
                <input type="number"
                       name="quantite"
                       id="quantite"
                       value="1"
                       min="1"
                       max="<?= $stock ?>"
                       required>
                <span class="hint">Maximum disponible&nbsp;: <?= $stock ?></span>
            </label>

            <!-- Live cost estimate -->
            <p id="montant-estime" class="hint" style="font-size:.95rem;color:var(--orange)"></p>

            <button class="btn btn-primary" type="submit">
                Envoyer la demande
            </button>
        </form>

    </div>
</section>

<script>
(function () {
    const PRIX  = <?= json_encode((float) $equipement['prix_jour']) ?>;
    const debut = document.getElementById('date_debut');
    const fin   = document.getElementById('date_fin');
    const qty   = document.getElementById('quantite');
    const out   = document.getElementById('montant-estime');

    function daysBetween(a, b) {
        const ms = new Date(b) - new Date(a);
        return ms > 0 ? Math.max(1, Math.round(ms / 86400000)) : 0;
    }

    function update() {
        if (!debut.value || !fin.value) { out.textContent = ''; return; }
        const days   = daysBetween(debut.value, fin.value);
        const q      = Math.max(1, parseInt(qty.value, 10) || 1);
        const total  = (PRIX * days * q).toFixed(2).replace('.', ',');
        out.textContent = days > 0
            ? `Estimation : ${total} € pour ${days} jour${days > 1 ? 's' : ''} × ${q} unité${q > 1 ? 's' : ''}`
            : '';
    }

    debut.addEventListener('change', update);
    fin.addEventListener('change',   update);
    qty.addEventListener('input',    update);
})();
</script>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
