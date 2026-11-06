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

class ActiviteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_cannot_start_before_it_has_a_task(): void
    {
        $admin = $this->makeUser('administrateur', 'Admin');
        $domain = Domaine::create(['name' => 'Infrastructure']);

        $response = $this->actingAs($admin)->post(route('activites.store'), [
            'title' => 'New project',
            'type' => 'projet',
            'start_date' => today()->toDateString(),
            'due_date' => today()->addDays(10)->toDateString(),
            'status' => 'en_cours',
            'priority' => 'haute',
            'domaine_id' => $domain->id,
            'responsable_id' => $admin->id,
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertSame(
            'Une activité doit avoir au moins une tâche avant de démarrer ou d’être terminée.',
            session('errors')->getBag('default')->first('status'),
        );
        $this->assertDatabaseMissing('activites', ['title' => 'New project']);
    }

    public function test_validating_an_activity_notifies_its_responsable(): void
    {
        Notification::fake([ActivityUpdateNotification::class]);
        $director = $this->makeUser('directeur', 'Director');
        $responsable = $this->makeUser('chef_service', 'Chef');
        $domain = Domaine::create(['name' => 'Infrastructure']);
        $activity = $this->makeActivity($responsable, $domain, 'Approved activity');

        $this->actingAs($director)->post(route('activites.validate', $activity))->assertRedirect();

        Notification::assertSentTo($responsable, ActivityUpdateNotification::class);
        $this->assertDatabaseHas('activites', ['id' => $activity->id, 'validated_by' => $director->id]);
    }

    private function makeUser(string $roleName, string $name): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], ['label' => $name]);
        $service = Service::firstOrCreate(['name' => 'Service '.$name]);

        return User::factory()->create(['role_id' => $role->id, 'service_id' => $service->id]);
    }

    private function makeActivity(User $responsable, Domaine $domain, string $title): Activite
    {
        return Activite::create([
            'title' => $title,
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
