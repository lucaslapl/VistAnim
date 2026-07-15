<footer>
    <div class="container flex space-between flex-wrap gap-20">

        <div class="about">
            <strong style="color: white;">🌿 À propos de la plateforme</strong>
            <p>{{ config('org.description') }}</p>
            <p>
                Réalisé avec <a href="#" target="_blank">{{ config('app.name') }}</a>,
                développé par <a href="#" target="_blank">Lucas LAPLANCHE</a>
            </p>
        </div>

        <div class="useful-links" style="text-align: right; min-width: 200px;">
            <strong style="color: white;">Liens utiles</strong>
            <ul style="list-style: none; padding: 0; margin-top: 5px;">
                <li><a href="{{ route('agenda') }}">Nos animations</a></li>
                <li><a href="{{ route('contact') }}">Contact</a></li>
                <li><a href="{{ route('mentions-legales') }}">Mentions légales</a></li>
                <li><a href="{{ route('cgu') }}">Conditions générales d'utilisation</a></li>
                <li><a href="{{ route('politique-confidentialite') }}">Politique de confidentialité</a></li>
                <li><a href="{{ route('admin.login') }}">Connexion administrateur</a></li>
            </ul>
        </div>

    </div>
</footer>
