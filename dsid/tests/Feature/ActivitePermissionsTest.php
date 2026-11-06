<?php

namespace Tests\Feature;

use App\Models\Domaine;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivitePermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_agents_and_readers_cannot_open_or_submit_the_creation_form(): void
    {
        $domain = Domaine::create(['name' => 'Infrastructure']);

        foreach (['agent', 'lecteur', 'directeur'] as $role) {
            $user = $this->makeUser($role);

            $this->actingAs($user)->get(route('activites.create'))->assertForbidden();
            $this->actingAs($user)->post(route('activites.store'), $this->payload($domain, $user))->assertForbidden();
        }

        $this->assertDatabaseCount('activites', 0);
    }

    public function test_creation_form_does_not_offer_statuses_that_need_tasks(): void
    {
        $chef = $this->makeUser('chef_service');
        Domaine::create(['name' => 'Infrastructure']);

        $this->actingAs($chef)->get(route('activites.create'))
            ->assertOk()
            ->assertSee('value="a_demarrer"', false)
            ->assertDontSee('value="en_cours"', false)
            ->assertDontSee('value="termine"', false);
    }

    public function test_a_chef_de_service_can_create_a_project_with_a_due_date_only(): void
    {
        $chef = $this->makeUser('chef_service');
        $domain = Domaine::create(['name' => 'Infrastructure']);

        $this->actingAs($chef)->post(route('activites.store'), $this->payload($domain, $chef, ['due_date' => null]))
            ->assertSessionHasErrors('due_date');

        $this->actingAs($chef)->post(route('activites.store'), $this->payload($domain, $chef))
            ->assertRedirect();

        $this->assertDatabaseHas('activites', ['title' => 'Migration du réseau', 'responsable_id' => $chef->id]);
    }

    private function payload(Domaine $domain, User $responsable, array $overrides = []): array
    {
        return array_merge([
            'title' => 'Migration du réseau',
            'type' => 'projet',
            'start_date' => today()->toDateString(),
            'due_date' => today()->addDays(30)->toDateString(),
            'status' => 'a_demarrer',
            'priority' => 'haute',
            'domaine_id' => $domain->id,
            'responsable_id' => $responsable->id,
        ], $overrides);
    }

    private function makeUser(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], ['label' => ucfirst($roleName)]);
        $service = Service::firstOrCreate(['name' => 'Service test']);

        return User::factory()->create(['role_id' => $role->id, 'service_id' => $service->id, 'is_active' => true]);
    }
}
