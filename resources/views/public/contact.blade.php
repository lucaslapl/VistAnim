@extends('layouts.public')

@section('content')
<div class="page-container">
    <h1>Contact</h1>
    <p class="text-muted">Une question, une suggestion ? N'hésitez pas à nous contacter.</p>

    <section style="margin-top: 30px;">
        <h2>Nos coordonnées</h2>
        <p>
            <strong>{{ config('org.name') }}</strong><br>
            @if (config('org.address')){{ config('org.address') }}<br>@endif
            @if (config('org.email'))Email : <a href="mailto:{{ config('org.email') }}">{{ config('org.email') }}</a><br>@endif
            @if (config('org.phone'))Tél : {{ config('org.phone') }}@endif
        </p>
    </section>

    <section style="margin-top: 30px;">
        <h2>Nous écrire</h2>
        <p>Vous pouvez nous envoyer un email directement à l'adresse suivante :</p>
        <p style="text-align:center; margin-top: 20px;">
            <a href="mailto:{{ config('org.email') }}" class="btn-primary">
                ✉️ Envoyer un email
            </a>
        </p>
        <p class="text-muted" style="font-size:0.9em; margin-top: 15px;">
            Nous nous efforçons de répondre à toutes les demandes sous 48 heures ouvrées.
        </p>
    </section>
</div>
@endsection
