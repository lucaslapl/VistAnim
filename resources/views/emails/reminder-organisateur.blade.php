<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rappel J-1 - Organisateur</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px;">
<div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden;">
    <div style="background: #8e44ad; padding: 20px; text-align: center;">
        <h1 style="color: white; margin: 0;">📋 Rappel J-1 — Récapitulatif</h1>
    </div>
    <div style="padding: 30px;">
        <p>Bonjour <strong>{{ $organizer_name }}</strong>,</p>
        <p>Voici le récapitulatif des inscrits pour votre animation <strong>{{ $event_title }}</strong> qui a lieu demain :</p>
        <table style="width:100%;border-collapse:collapse;margin:15px 0;">
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
                <td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Total inscrits</td>
                <td style="padding:8px;border:1px solid #ddd;"><strong>{{ (int)$total_inscrits }} personne(s)</strong></td>
            </tr>
        </table>

        @if (!empty($registrations))
        <h3 style="margin-top:20px;">Liste des participants</h3>
        <table style="width:100%;border-collapse:collapse;margin:10px 0;">
            <thead>
                <tr style="background:#8e44ad;color:white;">
                    <th style="padding:8px;border:1px solid #8e44ad;text-align:left;">Nom</th>
                    <th style="padding:8px;border:1px solid #8e44ad;text-align:left;">Email</th>
                    <th style="padding:8px;border:1px solid #8e44ad;text-align:left;">Tél.</th>
                    <th style="padding:8px;border:1px solid #8e44ad;text-align:center;">Places</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($registrations as $reg)
                <tr>
                    <td style="padding:8px;border:1px solid #ddd;">{{ $reg['firstname'] }} {{ $reg['lastname'] }}</td>
                    <td style="padding:8px;border:1px solid #ddd;">{{ $reg['email'] }}</td>
                    <td style="padding:8px;border:1px solid #ddd;">{{ $reg['phone'] ?? '—' }}</td>
                    <td style="padding:8px;border:1px solid #ddd;text-align:center;">{{ (int)$reg['nb_participants'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="background:#fff3cd;border:1px solid #ffeeba;color:#856404;padding:15px;border-radius:5px;margin-top:15px;">
            <p style="margin:0;"><strong>⚠️ Attention :</strong> Aucun inscrit pour le moment.</p>
        </div>
        @endif

        <p style="text-align:center;margin-top:20px;">
            <a href="{{ route('admin.inscriptions.lister', $event_id) }}"
               style="display:inline-block;padding:12px 24px;background:#8e44ad;color:white;text-decoration:none;border-radius:5px;">
                Voir / modifier les inscrits
            </a>
        </p>
    </div>
    <div style="background:#eee;padding:15px;text-align:center;color:#666;font-size:0.85em;">
        <p><a href="{{ route('admin.dashboard') }}" style="color:#8e44ad;">Accéder au tableau de bord</a></p>
    </div>
</div>
</body>
</html>
