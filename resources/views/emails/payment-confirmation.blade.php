<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Reçu de paiement</title></head>
<body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px;">
<div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden;">
    <div style="background: #155724; padding: 20px; text-align: center;">
        <h1 style="color: white; margin: 0; font-size: 1.4em;">🧾 REÇU DE PAIEMENT</h1>
    </div>
    <div style="padding: 30px;">
        <p>Bonjour <strong>{{ $firstname }}</strong>,</p>
        <p>Nous vous confirmons la réception de votre règlement pour l'animation suivante :</p>

        <table style="width:100%;border-collapse:collapse;margin:15px 0;">
            <tr><td style="padding:8px;border:1px solid #ddd;font-weight:bold;">N° de facture</td>
                <td style="padding:8px;border:1px solid #ddd;">{{ $invoice_number }}</td></tr>
            <tr><td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Date de paiement</td>
                <td style="padding:8px;border:1px solid #ddd;">{{ $payment_date }}</td></tr>
            <tr><td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Animation</td>
                <td style="padding:8px;border:1px solid #ddd;">{{ $event_title }}</td></tr>
            <tr><td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Date</td>
                <td style="padding:8px;border:1px solid #ddd;">{{ $event_date->format('d/m/Y à H\hi') }}</td></tr>
            <tr><td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Lieu</td>
                <td style="padding:8px;border:1px solid #ddd;">{{ $location }}</td></tr>
            @if ($rdv_point)
            <tr><td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Rendez-vous</td>
                <td style="padding:8px;border:1px solid #ddd;">{{ $rdv_point }}</td></tr>
            @endif
        </table>

        <h3 style="color: #155724; margin-top: 25px;">Détail du règlement</h3>
        <table style="width:100%;border-collapse:collapse;margin:10px 0;">
            <tr style="background:#155724;color:white;">
                <th style="padding:8px;border:1px solid #155724;text-align:left;">Libellé</th>
                <th style="padding:8px;border:1px solid #155724;text-align:center;">Prix unitaire</th>
                <th style="padding:8px;border:1px solid #155724;text-align:center;">Quantité</th>
                <th style="padding:8px;border:1px solid #155724;text-align:right;">Total</th>
            </tr>
            <tr>
                <td style="padding:8px;border:1px solid #ddd;">Inscription — {{ $event_title }}</td>
                <td style="padding:8px;border:1px solid #ddd;text-align:center;">{{ $price_per_person }}</td>
                <td style="padding:8px;border:1px solid #ddd;text-align:center;">{{ (int)$nb_participants }}</td>
                <td style="padding:8px;border:1px solid #ddd;text-align:right;"><strong>{{ $total }}</strong></td>
            </tr>
        </table>

        @if ($payment_intent_id)
        <p style="font-size:0.85em;color:#666;margin-top:10px;">
            Référence de transaction : <code>{{ $payment_intent_id }}</code><br>
            Moyen de paiement : Carte bancaire (Stripe)
        </p>
        @endif

        <p style="text-align:center;margin-top:25px;">
            <a href="{{ url('/reservation', $token) }}"
               style="display:inline-block;padding:12px 24px;background:#155724;color:white;text-decoration:none;border-radius:5px;">
                Gérer ma réservation
            </a>
        </p>
    </div>
    <div style="background:#eee;padding:15px;text-align:center;color:#666;font-size:0.85em;">
        <p>Merci pour votre participation ! 🌿</p>
        <p><a href="{{ url('/') }}" style="color:#155724;">{{ config('app.name') }}</a></p>
    </div>
</div>
</body>
</html>
