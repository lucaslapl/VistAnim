<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Animation annulée</title></head>
<body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px;">
<div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden;">
    <div style="background: #721c24; padding: 20px; text-align: center;">
        <h1 style="color: white; margin: 0;">Animation annulée</h1>
    </div>
    <div style="padding: 30px;">
        <p>Bonjour <strong>{{ $firstname }}</strong>,</p>
        <p>Nous sommes au regret de vous annoncer que l'animation
        <strong>{{ $event_title }}</strong> du {{ $event_date }}
        @if ($event_location)
        ({{ $event_location }})
        @endif
        a été <strong>annulée par l'organisme organisateur</strong>.</p>
        <p>Votre inscription est donc annulée. Nous vous prions de nous excuser pour la gêne occasionnée.</p>
        @if ($refunded)
        <div style="background:#d1ecf1;border:1px solid #bee5eb;color:#0c5460;padding:15px;border-radius:5px;margin-top:15px;">
            <p><strong>Remboursement</strong></p>
            <p>Le remboursement de votre participation a été initié. Il apparaîtra sous <strong>5 à 10 jours ouvrés</strong> sur votre compte bancaire.</p>
        </div>
        @endif
        <p style="margin-top:20px;">Nous vous invitons à consulter l'agenda pour découvrir les prochaines animations :
        <a href="{{ url('/agenda') }}" style="color:#2d6a4f;">voir l'agenda</a></p>
    </div>
    <div style="background:#eee;padding:15px;text-align:center;color:#666;font-size:0.85em;">
        <p>À bientôt pour une prochaine animation !</p>
    </div>
</div>
</body>
</html>
