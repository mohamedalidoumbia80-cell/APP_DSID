<?php

namespace Tests\Feature;

use App\Models\Activite;
use App\Models\Domaine;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_metrics_and_activity_list_only_include_activities_visible_to_the_agent(): void
    {
        $agent = $this->makeUser('agent', 'Agent');
        $admin = $this->makeUser('administrateur', 'Admin');
        $domain = Domaine::create(['name' => 'Infrastructure']);
        $visible = $this->makeActivity($agent, $domain, 'Visible activity', 'termine');
        $visible->taches()->create(['title' => 'Assigned task', 'agent_id' => $agent->id, 'status' => 'termine', 'progress' => 100]);
        $this->makeActivity($admin, $domain, 'Private activity', 'en_cours');

        $this->actingAs($agent)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('100%')
            ->assertSee('Infrastructure');
        $this->actingAs($agent)->get(route('activites.index'))
            ->assertOk()
            ->assertSee('Visible activity')
            ->assertDontSee('Private activity');
    }

    private function makeUser(string $roleName, string $name): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], ['label' => $name]);
        $service = Service::firstOrCreate(['name' => 'Service '.$name]);

        return User::factory()->create(['role_id' => $role->id, 'service_id' => $service->id]);
    }

    private function makeActivity(User $responsable, Domaine $domain, string $title, string $status = 'a_demarrer'): Activite
    {
        return Activite::create([
            'title' => $title,
            'type' => 'projet',
            'start_date' => today()->subDay(),
            'due_date' => today()->addDays(10),
            'status' => $status,
            'priority' => 'moyenne',
            'domaine_id' => $domain->id,
            'responsable_id' => $responsable->id,
        ]);
    }
}
