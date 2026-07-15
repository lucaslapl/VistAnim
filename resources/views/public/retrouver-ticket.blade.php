@extends('layouts.public')

@section('content')
<main class="container">
    <div style="max-width: 500px; margin: 40px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; width: 100%;">

        <h2>Recevoir mon lien de modification</h2>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>

            @if (session('debug_link') && app()->environment('local'))
                <div class="debug-box" style="margin-top: 15px; padding: 15px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 5px; color: #856404;">
                    <strong>⚙️ Mode Debug Local :</strong><br>
                    <p style="margin: 5px 0 10px 0; font-size: 0.9rem;">Voici le lien qui aurait dû être envoyé par e-mail :</p>
                    <a href="{{ session('debug_link') }}" class="btn-submit" style="display: inline-block; background: #856404; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 0.9rem;">
                        👉 Simuler le clic sur le lien du mail
                    </a>
                </div>
            @endif

            @php session()->forget('success'); @endphp

            <div style="margin-top: 20px; text-align: center;">
                <a href="{{ route('accueil') }}" class="btn-secondary" style="display: inline-block; padding: 10px 20px; background: #f8f9fa; border: 1px solid #ccc; text-decoration: none; color: #333; border-radius: 4px;">Retour à l'agenda</a>
            </div>

        @else

            <p>Pour des raisons de sécurité, nous devons valider votre identité. Confirmez votre adresse pour recevoir le lien d'accès.</p>

            <form action="{{ route('ticket.recuperer') }}" method="POST" style="margin-top: 20px;">
                @csrf
                <input type="hidden" name="event_id" value="{{ request('event_id') }}">

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="email">Votre adresse e-mail :</label>
                    <input type="email" name="email" id="email" required style="width: 100%; padding: 8px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px; background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #e3e6f0;">
                    <label for="captcha" style="display: block; margin-bottom: 5px; font-weight: bold;">
                        🛡️ Sécurité : Combien font {{ session('captcha_num1') }} + {{ session('captcha_num2') }} ?
                    </label>
                    <input type="number" name="captcha" id="captcha" required style="width: 80px; padding: 6px;" placeholder="?">
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; padding: 10px; cursor: pointer;">
                    ✉️ Recevoir mon lien sécurisé
                </button>
            </form>

            <div style="margin-top: 20px; text-align: center;">
                <a href="{{ route('accueil') }}">Retour à l'agenda</a>
            </div>

        @endif

    </div>
</main>
@endsection
