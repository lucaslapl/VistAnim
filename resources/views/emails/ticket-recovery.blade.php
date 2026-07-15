<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Récupération de ticket</title></head>
<body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px;">
<div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden;">
    <div style="background: #2d6a4f; padding: 20px; text-align: center;">
        <h1 style="color: white; margin: 0;">🔑 Votre lien d'accès</h1>
    </div>
    <div style="padding: 30px;">
        <p>Bonjour <strong>{{ $firstname }}</strong>,</p>
        <p>Voici votre lien sécurisé pour gérer votre réservation pour l'animation :</p>
        <p style="text-align:center;margin:20px 0;">
            <a href="{{ route('reservation.gerer', ['token' => $token]) }}"
               style="display:inline-block;padding:14px 28px;background:#2d6a4f;color:white;text-decoration:none;border-radius:5px;font-size:1.1em;">
                Accéder à ma réservation
            </a>
        </p>
        <div style="background:#fff3cd;border:1px solid #ffeeba;color:#856404;padding:10px;border-radius:5px;">
            <p><strong>🔒 Lien sécurisé :</strong> Ce lien est personnel et unique. Ne le partagez pas.</p>
            <p>Vous pouvez aussi copier-coller ce lien dans votre navigateur :</p>
            <p style="word-break:break-all;font-size:0.85em;">{{ route('reservation.gerer', ['token' => $token]) }}</p>
        </div>
    </div>
    <div style="background:#eee;padding:15px;text-align:center;color:#666;font-size:0.85em;">
        <p>Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.</p>
    </div>
</div>
</body>
</html>
