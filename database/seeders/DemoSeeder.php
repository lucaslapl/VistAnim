<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Seed VistAnim — Démo');
        $this->command->info(str_repeat('=', 40));

        // ─── 1. Nettoyage (FK-safe) ───
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('event_categories')->truncate();
        Registration::truncate();
        Event::truncate();
        Category::truncate();
        User::truncate();
        DB::table('event_drafts')->truncate();
        DB::table('admin_logs')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ─── 2. Users ───
        $usersData = [
            ['name' => 'Gestionnaire Municipal', 'email' => 'admin@nature-demo.fr',        'password' => 'demo1234', 'role' => 'admin'],
            ['name' => 'Les Amis de la Forêt',   'email' => 'contact@amis-foret.fr',        'password' => 'demo1234', 'role' => 'organisateur'],
            ['name' => 'Nature et Découverte',   'email' => 'contact@nature-decouverte.fr',  'password' => 'demo1234', 'role' => 'organisateur'],
            ['name' => 'Éveil Naturaliste',      'email' => 'contact@eveil-naturaliste.fr',  'password' => 'demo1234', 'role' => 'organisateur'],
        ];

        $users = [];
        foreach ($usersData as $u) {
            $users[$u['name']] = User::create([
                'name' => $u['name'],
                'email' => $u['email'],
                'password' => Hash::make($u['password']),
                'role' => $u['role'],
            ]);
            $this->command->info("   ✓ {$u['name']} ({$u['email']})");
        }

        // ─── 3. Catégories ───
        $categoryNames = ['Faune', 'Flore', 'Insectes', 'Oiseaux', 'Atelier pratique', 'Sortie nocturne', 'Famille', 'Adulte'];
        $categories = [];
        foreach ($categoryNames as $c) {
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $c), '-'));
            $categories[$c] = Category::create(['name' => $c, 'slug' => $slug]);
            $this->command->info("   ✓ $c");
        }

        // ─── 4. Events ───
        $now = Carbon::now();
        $eventsData = [
            [
                'title' => 'Initiation aux chants d\'oiseaux',
                'description' => 'Apprenez à reconnaître les oiseaux communs à leur chant. Sortie matinale guidée par un ornithologue passionné. Jumelles fournies sur demande. Prévoir des vêtements adaptés à la météo.',
                'event_date' => (clone $now)->addDays(7)->format('Y-m-d 08:30:00'),
                'location' => 'Forêt de Bellefeuille, parking nord',
                'rdv_point' => 'Parking principal, devant la maison forestière',
                'event_duration' => '2h30',
                'audience_type' => 'Tout public',
                'max_participants' => 20,
                'min_participants' => 4,
                'is_paid' => false,
                'price_details' => null,
                'price_amount' => null,
                'image' => null,
                'organizer' => 'Les Amis de la Forêt',
                'categories' => ['Oiseaux', 'Faune'],
            ],
            [
                'title' => 'Atelier construction de nichoirs',
                'description' => 'Venez construire et repartir avec votre propre nichoir à mésanges. Matériel fourni. Atelier encadré par un menuisier et un naturaliste. Possibilité d\'accrocher le nichoir dans votre jardin ou sur le site.',
                'event_date' => (clone $now)->addDays(10)->format('Y-m-d 14:00:00'),
                'location' => 'Maison de la Nature, Rue des Écureuils',
                'rdv_point' => 'Accueil de la Maison de la Nature',
                'event_duration' => '3h00',
                'audience_type' => 'Famille (enfants dès 6 ans)',
                'max_participants' => 12,
                'min_participants' => 3,
                'is_paid' => true,
                'price_details' => '5€ par participant. Gratuit pour les accompagnateurs. Paiement en ligne par carte bancaire.',
                'price_amount' => 5.00,
                'image' => null,
                'organizer' => 'Les Amis de la Forêt',
                'categories' => ['Atelier pratique', 'Famille', 'Faune'],
            ],
            [
                'title' => 'Sortie chauves-souris au crépuscule',
                'description' => 'À la tombée de la nuit, partez à la rencontre des chauves-souris avec un détecteur à ultrasons. Découvrez leur mode de vie, leur alimentation et les menaces qui pèsent sur ces mammifères fascinants.',
                'event_date' => (clone $now)->addDays(14)->format('Y-m-d 20:00:00'),
                'location' => 'Étang du Lac Vert',
                'rdv_point' => 'Ponton d\'observation',
                'event_duration' => '2h00',
                'audience_type' => 'Tout public',
                'max_participants' => 15,
                'min_participants' => 5,
                'is_paid' => false,
                'price_details' => null,
                'price_amount' => null,
                'image' => null,
                'organizer' => 'Nature et Découverte',
                'categories' => ['Sortie nocturne', 'Faune'],
            ],
            [
                'title' => 'Balade botanique en prairie',
                'description' => 'Découvrez les plantes sauvages comestibles et médicinales de nos prairies. Reconnaissance des espèces, anecdotes historiques et conseils de cueillette responsable. Carnet de notes bienvenu.',
                'event_date' => (clone $now)->addDays(18)->format('Y-m-d 09:30:00'),
                'location' => 'Sentier du Vieux Moulin',
                'rdv_point' => 'Aire de pique-nique du Vieux Moulin',
                'event_duration' => '3h00',
                'audience_type' => 'Adultes',
                'max_participants' => 15,
                'min_participants' => 3,
                'is_paid' => false,
                'price_details' => null,
                'price_amount' => null,
                'image' => null,
                'organizer' => 'Nature et Découverte',
                'categories' => ['Flore', 'Adulte'],
            ],
            [
                'title' => 'Stage photo nature',
                'description' => 'Stage d\'initiation à la photographie animalière et paysagère. Techniques de cadrage, réglages, affût et patience. Appareil reflex ou bridge recommandé, mais un smartphone peut suffire pour les bases.',
                'event_date' => (clone $now)->addDays(23)->format('Y-m-d 07:00:00'),
                'location' => 'Réserve naturelle du Bois des Sources',
                'rdv_point' => 'Portail d\'entrée de la réserve',
                'event_duration' => '4h00',
                'audience_type' => 'Adultes',
                'max_participants' => 10,
                'min_participants' => 4,
                'is_paid' => true,
                'price_details' => '8€ par personne. Paiement en ligne par carte bancaire.',
                'price_amount' => 8.00,
                'image' => null,
                'organizer' => 'Éveil Naturaliste',
                'categories' => ['Adulte', 'Faune', 'Flore'],
            ],
            [
                'title' => 'Chasse aux petites bêtes',
                'description' => 'Atelier pour les enfants : capture et observation des insectes à la loupe. Apprenez à reconnaître les familles d\'insectes, leur rôle dans la nature et fabriquez un petit carnet de terrain.',
                'event_date' => (clone $now)->addDays(30)->format('Y-m-d 10:00:00'),
                'location' => 'Parc municipal de la Mairie',
                'rdv_point' => 'Entrée du parc, côté fontaine',
                'event_duration' => '2h00',
                'audience_type' => 'Enfants (4-12 ans)',
                'max_participants' => 20,
                'min_participants' => 2,
                'is_paid' => false,
                'price_details' => null,
                'price_amount' => null,
                'image' => null,
                'organizer' => 'Éveil Naturaliste',
                'categories' => ['Insectes', 'Famille'],
            ],
            // Passées
            [
                'title' => 'Fresque participative de la biodiversité',
                'description' => 'Atelier collaboratif pour créer une fresque géante représentant la biodiversité locale. Chaque participant a contribué à une partie de l\'œuvre qui orne désormais le hall de la mairie.',
                'event_date' => (clone $now)->subDays(30)->format('Y-m-d 14:00:00'),
                'location' => 'Salle polyvalente',
                'rdv_point' => 'Entrée de la salle polyvalente',
                'event_duration' => '4h00',
                'audience_type' => 'Tout public',
                'max_participants' => 30,
                'min_participants' => 5,
                'is_paid' => false,
                'price_details' => null,
                'price_amount' => null,
                'image' => null,
                'organizer' => 'Les Amis de la Forêt',
                'categories' => ['Atelier pratique', 'Famille'],
            ],
            [
                'title' => 'Conférence : les abeilles sauvages',
                'description' => 'Conférence illustrée sur les abeilles solitaires, leur rôle vital dans la pollinisation et comment leur venir en aide dans nos jardins. Diaporama et échanges avec un apiculteur naturaliste.',
                'event_date' => (clone $now)->subDays(15)->format('Y-m-d 18:30:00'),
                'location' => 'Médiathèque centrale',
                'rdv_point' => 'Salle de conférence, 1er étage',
                'event_duration' => '2h00',
                'audience_type' => 'Adultes',
                'max_participants' => 25,
                'min_participants' => 5,
                'is_paid' => true,
                'price_details' => '3€ par personne. Paiement sur place possible.',
                'price_amount' => 3.00,
                'image' => null,
                'organizer' => 'Nature et Découverte',
                'categories' => ['Insectes', 'Adulte'],
            ],
        ];

        foreach ($eventsData as $e) {
            $event = Event::create([
                'title' => $e['title'],
                'description' => $e['description'],
                'event_date' => $e['event_date'],
                'location' => $e['location'],
                'rdv_point' => $e['rdv_point'],
                'event_duration' => $e['event_duration'],
                'audience_type' => $e['audience_type'],
                'max_participants' => $e['max_participants'],
                'min_participants' => $e['min_participants'],
                'is_paid' => $e['is_paid'],
                'price_details' => $e['price_details'],
                'price_amount' => $e['price_amount'],
                'image' => $e['image'],
                'organizer_id' => $users[$e['organizer']]->id,
            ]);

            $event->categories()->sync(
                collect($e['categories'])->map(fn ($c) => $categories[$c]->id)
            );

            $paid = $e['is_paid'] ? "{$e['price_amount']}€" : 'Gratuit';
            $date = Carbon::parse($e['event_date'])->format('d/m/Y');
            $this->command->info("   ✓ [{$date}] {$e['title']} — {$paid} ({$e['organizer']})");
        }

        // ─── 5. Registrations ───
        $events = Event::pluck('id', 'title');

        $registrationsData = [
            ['event' => 'Initiation aux chants d\'oiseaux',        'firstname' => 'Jean',    'lastname' => 'Martin',    'email' => 'jean.martin@email.fr',     'phone' => '0611223344', 'nb' => 1, 'paid' => null],
            ['event' => 'Initiation aux chants d\'oiseaux',        'firstname' => 'Marie',   'lastname' => 'Dubois',    'email' => 'marie.dubois@email.fr',    'phone' => '0622334455', 'nb' => 1, 'paid' => null],
            ['event' => 'Initiation aux chants d\'oiseaux',        'firstname' => 'Paul',    'lastname' => 'Petit',     'email' => 'paul.petit@email.fr',      'phone' => '0633445566', 'nb' => 3, 'paid' => null],
            ['event' => 'Atelier construction de nichoirs',        'firstname' => 'Lucie',   'lastname' => 'Leroy',     'email' => 'lucie.leroy@email.fr',     'phone' => '0644556677', 'nb' => 2, 'paid' => 'paid'],
            ['event' => 'Atelier construction de nichoirs',        'firstname' => 'Thomas',  'lastname' => 'Moreau',    'email' => 'thomas.moreau@email.fr',   'phone' => '0655667788', 'nb' => 1, 'paid' => 'pending'],
            ['event' => 'Sortie chauves-souris au crépuscule',    'firstname' => 'Sophie',  'lastname' => 'Roux',      'email' => 'sophie.roux@email.fr',     'phone' => '0611111111', 'nb' => 1, 'paid' => null],
            ['event' => 'Sortie chauves-souris au crépuscule',    'firstname' => 'Claire',  'lastname' => 'Garcia',    'email' => 'claire.garcia@email.fr',   'phone' => '0622222222', 'nb' => 1, 'paid' => null],
            ['event' => 'Sortie chauves-souris au crépuscule',    'firstname' => 'Julien',  'lastname' => 'Bernard',   'email' => 'julien.bernard@email.fr',  'phone' => '0633333333', 'nb' => 1, 'paid' => null],
            ['event' => 'Sortie chauves-souris au crépuscule',    'firstname' => 'Aurélie', 'lastname' => 'Simon',     'email' => 'aurelie.simon@email.fr',   'phone' => '0644444444', 'nb' => 3, 'paid' => null],
            ['event' => 'Sortie chauves-souris au crépuscule',    'firstname' => 'Nicolas', 'lastname' => 'Fournier',  'email' => 'nicolas.fournier@email.fr', 'phone' => '0655555555', 'nb' => 2, 'paid' => null],
            ['event' => 'Sortie chauves-souris au crépuscule',    'firstname' => 'Camille', 'lastname' => 'Lefevre',   'email' => 'camille.lefevre@email.fr', 'phone' => '0666666666', 'nb' => 1, 'paid' => null],
            ['event' => 'Sortie chauves-souris au crépuscule',    'firstname' => 'Antoine', 'lastname' => 'Mercier',   'email' => 'antoine.mercier@email.fr', 'phone' => '0677777777', 'nb' => 1, 'paid' => null],
            ['event' => 'Sortie chauves-souris au crépuscule',    'firstname' => 'Elodie',  'lastname' => 'Blanc',     'email' => 'elodie.blanc@email.fr',    'phone' => '0688888888', 'nb' => 1, 'paid' => null],
            ['event' => 'Sortie chauves-souris au crépuscule',    'firstname' => 'Romain',  'lastname' => 'Girard',    'email' => 'romain.girard@email.fr',   'phone' => '0699999999', 'nb' => 2, 'paid' => null],
            ['event' => 'Sortie chauves-souris au crépuscule',    'firstname' => 'Manon',   'lastname' => 'Bonnet',    'email' => 'manon.bonnet@email.fr',    'phone' => '0600000001', 'nb' => 1, 'paid' => null],
            ['event' => 'Sortie chauves-souris au crépuscule',    'firstname' => 'Hugo',    'lastname' => 'Vincent',   'email' => 'hugo.vincent@email.fr',    'phone' => '0600000002', 'nb' => 1, 'paid' => null],
            ['event' => 'Balade botanique en prairie',            'firstname' => 'Sophie',  'lastname' => 'Roux',      'email' => 'sophie.roux@email.fr',     'phone' => '0611111111', 'nb' => 1, 'paid' => null],
            ['event' => 'Balade botanique en prairie',            'firstname' => 'Claire',  'lastname' => 'Garcia',    'email' => 'claire.garcia@email.fr',   'phone' => '0622222222', 'nb' => 1, 'paid' => null],
            ['event' => 'Balade botanique en prairie',            'firstname' => 'Julien',  'lastname' => 'Bernard',   'email' => 'julien.bernard@email.fr',  'phone' => '0633333333', 'nb' => 1, 'paid' => null],
            ['event' => 'Balade botanique en prairie',            'firstname' => 'Isabelle', 'lastname' => 'Robert',    'email' => 'isabelle.robert@email.fr', 'phone' => '0600000003', 'nb' => 2, 'paid' => null],
            ['event' => 'Stage photo nature',                     'firstname' => 'Pierre',  'lastname' => 'Durand',    'email' => 'pierre.durand@email.fr',   'phone' => '0600000004', 'nb' => 1, 'paid' => 'paid'],
            ['event' => 'Stage photo nature',                     'firstname' => 'Camille', 'lastname' => 'Lefevre',   'email' => 'camille.lefevre@email.fr', 'phone' => '0666666666', 'nb' => 1, 'paid' => 'paid'],
            ['event' => 'Chasse aux petites bêtes',               'firstname' => 'Léa',     'lastname' => 'Morel',     'email' => 'lea.morel@email.fr',       'phone' => '0600000005', 'nb' => 2, 'paid' => null],
            ['event' => 'Fresque participative de la biodiversité', 'firstname' => 'Anne',    'lastname' => 'Lambert',   'email' => 'anne.lambert@email.fr',    'phone' => '0600000010', 'nb' => 2, 'paid' => null],
            ['event' => 'Fresque participative de la biodiversité', 'firstname' => 'David',   'lastname' => 'Colin',     'email' => 'david.colin@email.fr',     'phone' => '0600000011', 'nb' => 1, 'paid' => null],
            ['event' => 'Fresque participative de la biodiversité', 'firstname' => 'Sarah',   'lastname' => 'Joly',      'email' => 'sarah.joly@email.fr',      'phone' => '0600000012', 'nb' => 1, 'paid' => null],
            ['event' => 'Fresque participative de la biodiversité', 'firstname' => 'Lucas',   'lastname' => 'Chevalier', 'email' => 'lucas.chevalier@email.fr', 'phone' => '0600000013', 'nb' => 3, 'paid' => null],
            ['event' => 'Fresque participative de la biodiversité', 'firstname' => 'Emma',    'lastname' => 'Garnier',   'email' => 'emma.garnier@email.fr',    'phone' => '0600000014', 'nb' => 1, 'paid' => null],
            ['event' => 'Conférence : les abeilles sauvages',     'firstname' => 'Nicolas', 'lastname' => 'Fournier',  'email' => 'nicolas.fournier@email.fr', 'phone' => '0655555555', 'nb' => 1, 'paid' => 'paid'],
            ['event' => 'Conférence : les abeilles sauvages',     'firstname' => 'Amandine', 'lastname' => 'Picard',    'email' => 'amandine.picard@email.fr', 'phone' => '0600000015', 'nb' => 2, 'paid' => 'refunded'],
            ['event' => 'Conférence : les abeilles sauvages',     'firstname' => 'Hugo',    'lastname' => 'Vincent',   'email' => 'hugo.vincent@email.fr',    'phone' => '0600000002', 'nb' => 1, 'paid' => 'paid'],
        ];

        foreach ($registrationsData as $r) {
            Registration::create([
                'event_id' => $events[$r['event']],
                'firstname' => $r['firstname'],
                'lastname' => $r['lastname'],
                'email' => $r['email'],
                'phone' => $r['phone'],
                'nb_participants' => $r['nb'],
                'token' => Str::random(64),
                'consent' => true,
                'user_ip' => '127.0.0.1',
                'payment_status' => $r['paid'],
                'payment_intent_id' => $r['paid'] === 'paid' ? 'pi_demo_'.Str::random(16) : null,
            ]);
            $this->command->info("   ✓ {$r['firstname']} {$r['lastname']} → {$r['event']}".($r['paid'] ? " [{$r['paid']}]" : ''));
        }

        // ─── Récapitulatif ───
        $this->command->info('');
        $this->command->info(str_repeat('=', 40));
        $this->command->info('🌱 Seed terminé !');
        $this->command->info('');
        $this->command->info('━━━ Connexion admin ──────────────────────');
        $this->command->info('   Email  : admin@nature-demo.fr');
        $this->command->info('   Mot de passe : demo1234');
        $this->command->info('──────────────────────────────────────────');
    }
}
