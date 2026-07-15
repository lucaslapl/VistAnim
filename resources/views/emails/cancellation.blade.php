<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Annulation confirmée</title></head>
<body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px;">
<div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden;">
    <div style="background: #856404; padding: 20px; text-align: center;">
        <h1 style="color: white; margin: 0;">Annulation confirmée</h1>
    </div>
    <div style="padding: 30px;">
        <p>Bonjour <strong>{{ $firstname }}</strong>,</p>
        <p>Votre inscription pour l'animation <strong>{{ $event_title }}</strong>
        du {{ $event_date->format('d/m/Y à H\hi') }}
        @if ($isAdminDeletion)
        a été annulée par un administrateur.</p>
        <p>Si vous avez des questions, n'hésitez pas à contacter l'organisation.</p>
        @else
        a bien été annulée.</p>
        <p>Les places ont été libérées pour d'autres participants. Merci de nous avoir prévenus !</p>
        @endif
        @if ($refunded)
        <div style="background:#d1ecf1;border:1px solid #bee5eb;color:#0c5460;padding:15px;border-radius:5px;margin-top:15px;">
            <p><strong>💳 Remboursement</strong></p>
            <p>Le remboursement a été initié. Il apparaîtra sous <strong>5 à 10 jours ouvrés</strong> sur votre compte bancaire.</p>
        </div>
        @endif
        <p style="margin-top:20px;"><a href="{{ url('/') }}" style="color:#2d6a4f;">Retour à l'accueil</a></p>
    </div>
    <div style="background:#eee;padding:15px;text-align:center;color:#666;font-size:0.85em;">
        <p>À bientôt pour une prochaine animation ! 🌿</p>
    </div>
</div>
</body>
</html>
