<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_organizer_can_access_dashboard(): void
    {
        $organizer = User::factory()->create();

        $this->actingAs($organizer)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_unknown_role_gets_403(): void
    {
        $user = User::factory()->create(['role' => 'intrus']);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertStatus(403);
    }

    public function test_organizer_cannot_edit_another_organizers_event(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $event = Event::factory()->for($owner, 'organizer')->create();

        $this->actingAs($intruder)
            ->get(route('admin.evenements.modifier', $event->id))
            ->assertStatus(403);

        $this->actingAs($intruder)
            ->post("/admin/evenements/{$event->id}/supprimer")
            ->assertStatus(403);

        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    public function test_admin_can_edit_any_event(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $event = Event::factory()->for($owner, 'organizer')->create();

        $this->actingAs($admin)
            ->get(route('admin.evenements.modifier', $event->id))
            ->assertOk();
    }

    public function test_structures_management_is_admin_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $organizer = User::factory()->create();

        $this->actingAs($admin)->get(route('admin.structures.lister'))->assertOk();
        $this->actingAs($organizer)->get(route('admin.structures.lister'))->assertStatus(403);
    }

    public function test_login_with_valid_credentials_redirects_to_dashboard(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret123')]);

        $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $user = User::factory()->create();

        $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_failures(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $i) {
            $this->post('/admin/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Trop de tentatives', session('errors')->first('email'));
    }
}
