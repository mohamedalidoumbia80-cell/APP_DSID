<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Models\Activite;
use App\Models\Domaine;
use App\Models\JournalAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_the_login_page(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
        $this->get(route('login'))->assertOk()->assertSee('Connectez-vous');
    }

    public function test_agent_can_open_dashboard_but_not_user_administration(): void
    {
        $role = Role::create(['name' => 'agent', 'label' => 'Agent']);
        $service = Service::create(['name' => 'Service DSID']);
        $user = User::factory()->create(['role_id' => $role->id, 'service_id' => $service->id]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_inactive_user_cannot_access_the_application(): void
    {
        $role = Role::create(['name' => 'lecteur', 'label' => 'Lecteur']);
        $service = Service::create(['name' => 'Service DSID']);
        $user = User::factory()->create(['role_id' => $role->id, 'service_id' => $service->id, 'is_active' => false]);

        $this->actingAs($user)->get(route('dashboard'))->assertForbidden();
    }

    public function test_active_user_can_log_in(): void
    {
        $role = Role::create(['name' => 'lecteur', 'label' => 'Lecteur']);
        $service = Service::create(['name' => 'Service DSID']);
        $user = User::factory()->create(['role_id' => $role->id, 'service_id' => $service->id]);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_activity_progress_is_aggregated_from_tasks_and_lateness_is_calculated(): void
    {
        $adminRole = Role::create(['name' => 'administrateur', 'label' => 'Administrateur']);
        $agentRole = Role::create(['name' => 'agent', 'label' => 'Agent']);
        $service = Service::create(['name' => 'Service DSID']);
        $admin = User::factory()->create(['role_id' => $adminRole->id, 'service_id' => $service->id]);
        $agent = User::factory()->create(['role_id' => $agentRole->id, 'service_id' => $service->id]);
        $domain = Domaine::create(['name' => 'Sécurité des systèmes d’information']);

        $this->actingAs($admin)->post(route('activites.store'), [
            'title' => 'Modernisation du réseau',
            'type' => 'projet',
            'start_date' => today()->subDays(10)->toDateString(),
            'due_date' => today()->subDay()->toDateString(),
            'status' => 'a_demarrer',
            'priority' => 'haute',
            'domaine_id' => $domain->id,
            'responsable_id' => $admin->id,
        ])->assertRedirect();

        $activity = Activite::firstOrFail();
        $this->actingAs($admin)->post(route('activites.taches.store', $activity), [
            'title' => 'Configurer le pare-feu',
            'agent_id' => $agent->id,
            'due_date' => today()->addDay()->toDateString(),
        ])->assertRedirect();

        $task = $activity->taches()->firstOrFail();
        $this->actingAs($agent)->put(route('taches.update', $task), [
            'status' => 'en_cours',
            'progress' => 40,
        ])->assertRedirect();

        $this->assertSame(40, $activity->fresh()->progress);
        $this->assertTrue($activity->fresh()->is_late);
        $this->assertGreaterThanOrEqual(3, JournalAction::count());
    }
}
