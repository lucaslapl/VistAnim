<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rappel J-1</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px;">
<div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden;">
    <div style="background: #e67e22; padding: 20px; text-align: center;">
        <h1 style="color: white; margin: 0;">🔔 Rappel : c'est demain !</h1>
    </div>
    <div style="padding: 30px;">
        <p>Bonjour <strong>{{ $firstname }}</strong>,</p>
        <p>Nous vous rappelons que votre animation <strong>{{ $event_title }}</strong> a lieu demain !</p>
        <table style="width:100%;border-collapse:collapse;margin:15px 0;">
            <tr>
                <td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Date</td>
                <td style="padding:8px;border:1px solid #ddd;">{{ $event_date->format('d/m/Y à H\hi') }}</td>
            </tr>
            @if ($event_duration)
            <tr>
                <td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Durée</td>
                <td style="padding:8px;border:1px solid #ddd;">{{ $event_duration }}</td>
            </tr>
            @endif
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
        <p style="margin-top:10px;">Vous pouvez modifier ou annuler votre réservation jusqu'au dernier moment :</p>
        <p style="text-align:center;margin-top:20px;">
            <a href="{{ route('reservation.gerer', ['token' => $token]) }}"
               style="display:inline-block;padding:12px 24px;background:#e67e22;color:white;text-decoration:none;border-radius:5px;">
                Gérer ma réservation
            </a>
        </p>
    </div>
    <div style="background:#eee;padding:15px;text-align:center;color:#666;font-size:0.85em;">
        <p>À bientôt sur le terrain ! 🌿</p>
        <p><a href="{{ url('/') }}" style="color:#e67e22;">{{ config('app.name') }}</a></p>
    </div>
</div>
</body>
</html>
