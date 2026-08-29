document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('location-form');
    const estimate = document.getElementById('montant-estime');
    if (!form || !estimate || typeof window.LOCATION_PRIX_JOUR !== 'number') {
        return;
    }

    const debut = form.querySelector('[name="date_debut"]');
    const fin = form.querySelector('[name="date_fin"]');

    const update = () => {
        if (!debut.value || !fin.value) {
            return;
        }
        const start = new Date(debut.value);
        const end = new Date(fin.value);
        if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || end < start) {
            estimate.textContent = 'Dates invalides.';
            return;
        }
        const days = Math.max(1, Math.round((end - start) / 86400000));
        const total = days * window.LOCATION_PRIX_JOUR;
        estimate.textContent = `Estimation : ${days} jour(s) × ${window.LOCATION_PRIX_JOUR.toFixed(2)} € = ${total.toFixed(2)} €`;
    };

    debut.addEventListener('change', update);
    fin.addEventListener('change', update);
});
