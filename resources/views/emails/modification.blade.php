<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Modification confirmée</title></head>
<body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px;">
<div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden;">
    <div style="background: #2d6a4f; padding: 20px; text-align: center;">
        <h1 style="color: white; margin: 0;">📝 Modification confirmée</h1>
    </div>
    <div style="padding: 30px;">
        <p>Bonjour <strong>{{ $firstname }}</strong>,</p>
        <p>Votre inscription pour <strong>{{ $event_title }}</strong>
        du {{ $event_date->format('d/m/Y à H\hi') }} a été modifiée :</p>
        <table style="width:100%;border-collapse:collapse;margin:15px 0;">
            <tr><td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Ancien nombre</td>
                <td style="padding:8px;border:1px solid #ddd;">{{ (int)$old_nb }} personne(s)</td></tr>
            <tr><td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Nouveau nombre</td>
                <td style="padding:8px;border:1px solid #ddd;"><strong>{{ (int)$new_nb }} personne(s)</strong></td></tr>
        </table>
        @if ($refund_info)
        <div style="background:#d1ecf1;border:1px solid #bee5eb;color:#0c5460;padding:15px;border-radius:5px;margin-top:15px;">
            <p>💳 <strong>{{ $refund_info }}</strong></p>
        </div>
        @endif
        <p style="text-align:center;margin-top:20px;">
            <a href="{{ route('reservation.gerer', ['token' => $token]) }}"
               style="display:inline-block;padding:12px 24px;background:#2d6a4f;color:white;text-decoration:none;border-radius:5px;">
                Voir ma réservation
            </a>
        </p>
    </div>
    <div style="background:#eee;padding:15px;text-align:center;color:#666;font-size:0.85em;">
        <p>Merci pour votre confiance ! 🌿</p>
    </div>
</div>
</body>
</html>
