<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrat de location #<?= (int) $location['id'] ?></title>
    <style>
        /* ── Reset ────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #1a1a2e;
            background: #f5f5f5;
        }

        /* ── Screen wrapper (hidden when printing) ────────────── */
        .screen-toolbar {
            background: #1a1a2e;
            color: #fff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .screen-toolbar h2 { font-size: 15px; flex: 1; }
        .btn-print {
            background: #e63946;
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            font-weight: 600;
            letter-spacing: .4px;
        }
        .btn-print:hover { background: #c1121f; }
        .btn-back {
            color: #aaa;
            text-decoration: none;
            font-size: 13px;
        }
        .btn-back:hover { color: #fff; }

        /* ── Page ─────────────────────────────────────────────── */
        .page {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            padding: 48px 56px;
            border-radius: 6px;
            box-shadow: 0 2px 16px rgba(0,0,0,.12);
        }

        /* ── Header ───────────────────────────────────────────── */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #1a1a2e;
            padding-bottom: 20px;
            margin-bottom: 28px;
        }
        .doc-header .brand { font-size: 22px; font-weight: 700; color: #1a1a2e; letter-spacing: 1px; }
        .doc-header .brand span { color: #e63946; }
        .doc-header .doc-meta { text-align: right; }
        .doc-meta .doc-title { font-size: 18px; font-weight: 700; color: #1a1a2e; }
        .doc-meta .doc-ref  { font-size: 12px; color: #666; margin-top: 4px; }

        /* ── Badge statut ─────────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .badge-en-attente  { background:#fff3cd; color:#856404; }
        .badge-confirmée   { background:#d1e7dd; color:#0a3622; }
        .badge-en-cours    { background:#cfe2ff; color:#084298; }
        .badge-terminée    { background:#e2e3e5; color:#41464b; }
        .badge-annulée     { background:#f8d7da; color:#58151c; }

        /* ── Parties ──────────────────────────────────────────── */
        .parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
        }
        .party-box {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 16px 20px;
        }
        .party-box h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #888;
            margin-bottom: 8px;
        }
        .party-box p { line-height: 1.7; }
        .party-box strong { color: #1a1a2e; }

        /* ── Detail table ─────────────────────────────────────── */
        .section-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #888;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 6px;
            margin-bottom: 14px;
            margin-top: 24px;
        }

        table.detail {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        table.detail th {
            background: #f8f9fa;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #555;
            padding: 8px 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
        }
        table.detail td {
            padding: 10px 12px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;
        }
        table.detail tr:last-child td { border-bottom: none; }

        /* ── Totals ───────────────────────────────────────────── */
        .totals {
            margin-left: auto;
            width: 300px;
        }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 6px 10px; font-size: 13px; }
        .totals td:first-child { color: #555; }
        .totals td:last-child  { text-align: right; font-weight: 600; }
        .totals tr.grand-total td {
            border-top: 2px solid #1a1a2e;
            font-size: 15px;
            padding-top: 10px;
            color: #1a1a2e;
        }

        /* ── Signature strip ──────────────────────────────────── */
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 48px;
        }
        .sig-box { border-top: 1px solid #ccc; padding-top: 10px; font-size: 12px; color: #666; }
        .sig-box p { margin-bottom: 4px; }
        .sig-box .sig-name { font-weight: 700; color: #1a1a2e; }

        /* ── Footer ───────────────────────────────────────────── */
        .doc-footer {
            margin-top: 48px;
            border-top: 1px solid #e0e0e0;
            padding-top: 12px;
            font-size: 11px;
            color: #aaa;
            text-align: center;
        }

        /* ── Print styles ─────────────────────────────────────── */
        @media print {
            body { background: #fff; }
            .screen-toolbar { display: none; }
            .page {
                max-width: 100%;
                margin: 0;
                padding: 20mm 18mm;
                box-shadow: none;
                border-radius: 0;
            }
        }

        @page {
            size: A4;
            margin: 0;
        }
    </style>
</head>
<body>

<!-- ── Screen toolbar (hidden on print) ─────────────────────────── -->
<div class="screen-toolbar">
    <h2>Aperçu du contrat #<?= (int) $location['id'] ?></h2>
    <a class="btn-back" href="javascript:history.back()">← Retour</a>
    <button class="btn-print" onclick="window.print()">🖨 Imprimer / Enregistrer en PDF</button>
</div>

<!-- ── Printable page ─────────────────────────────────────────────── -->
<div class="page">

    <!-- Header -->
    <div class="doc-header">
        <div class="brand">LOCA<span>TECH</span></div>
        <div class="doc-meta">
            <div class="doc-title">CONTRAT DE LOCATION</div>
            <div class="doc-ref">Réf. #<?= str_pad((string)(int)$location['id'], 6, '0', STR_PAD_LEFT) ?></div>
            <div class="doc-ref" style="margin-top:6px;">
                <?php
                $statut = $location['statut'];
                $cls = 'badge-' . str_replace(' ', '-', $statut);
                ?>
                <span class="badge <?= e($cls) ?>"><?= e($statut) ?></span>
            </div>
        </div>
    </div>

    <!-- Parties -->
    <div class="parties">
        <div class="party-box">
            <h3>Prestataire</h3>
            <p><strong>LOCATECH SARL</strong></p>
            <p>123 Rue de l'Innovation</p>
            <p>75000 Paris, France</p>
            <p>contact@locatech.fr</p>
        </div>
        <div class="party-box">
            <h3>Locataire</h3>
            <p><strong><?= e($location['utilisateur_prenom'] . ' ' . $location['utilisateur_nom']) ?></strong></p>
            <p><?= e($location['utilisateur_email']) ?></p>
        </div>
    </div>

    <!-- Equipment detail -->
    <div class="section-title">Détail de la location</div>
    <table class="detail">
        <thead>
            <tr>
                <th>Équipement</th>
                <th>Date de début</th>
                <th>Date de fin</th>
                <th>Nbre de jours</th>
                <th>Prix / jour</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $jours = days_between($location['date_debut'], $location['date_fin']);
            ?>
            <tr>
                <td><?= e($location['equipement_nom']) ?></td>
                <td><?= e($location['date_debut']) ?></td>
                <td><?= e($location['date_fin']) ?></td>
                <td><?= $jours ?> jour<?= $jours > 1 ? 's' : '' ?></td>
                <td><?= number_format((float) $location['prix_jour'], 2, ',', ' ') ?> €</td>
            </tr>
        </tbody>
    </table>

    <!-- Totals -->
    <?php
        $base      = (float) $location['prix_jour'] * $jours;
        $frais     = (float) $location['frais_additionnels'];
        $total     = (float) $location['montant_total'];
    ?>
    <div class="totals">
        <table>
            <tr>
                <td>Sous-total</td>
                <td><?= number_format($base, 2, ',', ' ') ?> €</td>
            </tr>
            <?php if ($frais > 0): ?>
            <tr>
                <td>Frais additionnels</td>
                <td><?= number_format($frais, 2, ',', ' ') ?> €</td>
            </tr>
            <?php endif; ?>
            <tr class="grand-total">
                <td>Total TTC</td>
                <td><?= number_format($total, 2, ',', ' ') ?> €</td>
            </tr>
        </table>
    </div>

    <!-- Conditions résumées -->
    <div class="section-title" style="margin-top:36px;">Conditions générales (résumé)</div>
    <p style="font-size:12px;color:#555;line-height:1.8;">
        Le matériel est loué en l'état et doit être restitué dans le même état à la date de fin
        prévue. Tout retard entraîne des frais supplémentaires au tarif journalier contractuel.
        Le locataire est responsable de tout dommage survenu pendant la durée de la location.
        En cas de litige, les parties s'engagent à rechercher une solution amiable avant tout
        recours judiciaire.
    </p>

    <!-- Signatures -->
    <div class="signatures">
        <div class="sig-box">
            <p>Signature du prestataire</p>
            <p class="sig-name">LOCATECH SARL</p>
            <br><br><br>
            <p>___________________________</p>
        </div>
        <div class="sig-box">
            <p>Signature du locataire</p>
            <p class="sig-name"><?= e($location['utilisateur_prenom'] . ' ' . $location['utilisateur_nom']) ?></p>
            <br><br><br>
            <p>___________________________</p>
        </div>
    </div>

    <!-- Doc footer -->
    <div class="doc-footer">
        Document généré le <?= date('d/m/Y à H:i') ?> — LOCATECH SARL • SIRET 000 000 000 00000 • contact@locatech.fr
    </div>

</div><!-- /.page -->

<script>
    // Auto-trigger print dialog only when URL contains ?print=1
    if (new URLSearchParams(window.location.search).get('print') === '1') {
        window.addEventListener('load', function () { window.print(); });
    }
</script>
</body>
</html>
