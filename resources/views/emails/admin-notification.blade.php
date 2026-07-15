<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Nouvelle inscription</title></head>
<body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px;">
<div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden;">
    <div style="background: #0056b3; padding: 20px; text-align: center;">
        <h1 style="color: white; margin: 0;">📢 Nouvelle inscription</h1>
    </div>
    <div style="padding: 30px;">
        <p>Bonjour <strong>{{ $organizer_name }}</strong>,</p>
        <p>Un nouveau participant s'est inscrit à votre animation :</p>
        <table style="width:100%;border-collapse:collapse;margin:15px 0;">
            <tr><td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Animation</td>
                <td style="padding:8px;border:1px solid #ddd;">{{ $event_title }}</td></tr>
            <tr><td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Date</td>
                <td style="padding:8px;border:1px solid #ddd;">{{ $event_date->format('d/m/Y à H\hi') }}</td></tr>
            <tr><td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Participant</td>
                <td style="padding:8px;border:1px solid #ddd;">{{ $firstname }} {{ $lastname }}</td></tr>
            <tr><td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Email</td>
                <td style="padding:8px;border:1px solid #ddd;">{{ $email }}</td></tr>
            <tr><td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Téléphone</td>
                <td style="padding:8px;border:1px solid #ddd;">{{ $phone }}</td></tr>
            <tr><td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Places réservées</td>
                <td style="padding:8px;border:1px solid #ddd;">{{ (int)$nb_participants }} personne(s)</td></tr>
        </table>
        <p style="text-align:center;margin-top:20px;">
            <a href="{{ route('admin.inscriptions.lister', $event_id) }}"
               style="display:inline-block;padding:12px 24px;background:#0056b3;color:white;text-decoration:none;border-radius:5px;">
                Voir tous les inscrits
            </a>
        </p>
    </div>
    <div style="background:#eee;padding:15px;text-align:center;color:#666;font-size:0.85em;">
        <p><a href="{{ route('admin.dashboard') }}" style="color:#0056b3;">Accéder au tableau de bord</a></p>
    </div>
</div>
</body>
</html>
