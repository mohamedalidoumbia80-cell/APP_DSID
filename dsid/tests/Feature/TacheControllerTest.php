<?php

namespace Tests\Feature;

use App\Models\Activite;
use App\Models\Domaine;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Notifications\ActivityUpdateNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TacheControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigning_a_task_notifies_the_agent_and_persists_the_assignment(): void
    {
        Notification::fake([ActivityUpdateNotification::class]);
        $chef = $this->makeUser('chef_service', 'Chef');
        $agent = $this->makeUser('agent', 'Agent');
        $domain = Domaine::create(['name' => 'Infrastructure']);
        $activity = $this->makeActivity($chef, $domain);

        $this->actingAs($chef)->post(route('activites.taches.store', $activity), [
            'title' => 'Configure router',
            'agent_id' => $agent->id,
        ])->assertRedirect();

        Notification::assertSentTo($agent, ActivityUpdateNotification::class);
        $this->assertDatabaseHas('taches', ['title' => 'Configure router', 'agent_id' => $agent->id]);
    }

    private function makeUser(string $roleName, string $name): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], ['label' => $name]);
        $service = Service::firstOrCreate(['name' => 'Service '.$name]);

        return User::factory()->create(['role_id' => $role->id, 'service_id' => $service->id]);
    }

    private function makeActivity(User $responsable, Domaine $domain): Activite
    {
        return Activite::create([
            'title' => 'Network upgrade',
            'type' => 'projet',
            'start_date' => today()->subDay(),
            'due_date' => today()->addDays(10),
            'status' => 'a_demarrer',
            'priority' => 'moyenne',
            'domaine_id' => $domain->id,
            'responsable_id' => $responsable->id,
        ]);
    }
}
