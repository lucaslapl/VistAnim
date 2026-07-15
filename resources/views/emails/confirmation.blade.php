<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden;">
        <div style="background: #2d6a4f; padding: 20px; text-align: center;">
            <h1 style="color: white; margin: 0;">✅ Inscription confirmée</h1>
        </div>
        <div style="padding: 30px;">
            <p>Bonjour <strong>{{ $firstname }}</strong>,</p>
            <p>Votre inscription pour l'animation suivante a bien été enregistrée :</p>
            <table style="width:100%;border-collapse:collapse;margin:15px 0;">
                <tr>
                    <td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Animation</td>
                    <td style="padding:8px;border:1px solid #ddd;">{{ $event_title }}</td>
                </tr>
                <tr>
                    <td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Date</td>
                    <td style="padding:8px;border:1px solid #ddd;">{{ $event_date->format('d/m/Y à H\hi') }}</td>
                </tr>
                <tr>
                    <td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Lieu</td>
                    <td style="padding:8px;border:1px solid #ddd;">{{ $location }}</td>
                </tr>
                @if ($rdv_point)
                <tr>
                    <td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Rendez-vous</td>
                    <td style="padding:8px;border:1px solid #ddd;">{{ $rdv_point }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Places réservées</td>
                    <td style="padding:8px;border:1px solid #ddd;">{{ (int)$nb_participants }} personne(s)</td>
                </tr>
            </table>
            @if ($is_paid && $payment_status === 'paid')
                <div style="background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:15px;border-radius:5px;margin-top:15px;">
                    <p style="margin:0;"><strong>✅ Paiement effectué</strong></p>
                    <p style="margin:5px 0 0;">Montant réglé : <strong>{{ $total }}</strong></p>
                    <p style="margin:5px 0 0;">Votre règlement a bien été reçu. Un récapitulatif détaillé vous a été envoyé par email.</p>
                </div>
            @elseif ($is_paid)
                <p>💳 <strong>Animation payante</strong> — Merci de procéder au paiement via votre espace réservation.</p>
                <p>Montant à régler : <strong>{{ $total }}</strong></p>
            @endif
            <p style="text-align:center;margin-top:20px;">
                <a href="{{ url('/reservation', $token) }}"
                    style="display:inline-block;padding:12px 24px;background:#2d6a4f;color:white;text-decoration:none;border-radius:5px;">
                    Gérer ma réservation
                </a>
            </p>
        </div>
        <div style="background:#eee;padding:15px;text-align:center;color:#666;font-size:0.85em;">
            <p>Merci pour votre engagement ! 🌿</p>
            <p><a href="{{ url('/') }}" style="color:#2d6a4f;">{{ config('app.name') }}</a></p>
        </div>
    </div>
</body>
</html>
