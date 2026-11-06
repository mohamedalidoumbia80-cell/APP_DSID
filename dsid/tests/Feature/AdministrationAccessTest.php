<?php

namespace Tests\Feature;

use App\Models\Domaine;
use App\Models\JournalAction;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministrationAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_sees_the_domains_link_and_can_create_a_domain(): void
    {
        $admin = $this->makeUser('administrateur');

        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee(route('admin.domaines.index'), false);

        $this->actingAs($admin)->post(route('admin.domaines.store'), ['name' => 'Cybersécurité'])->assertRedirect();

        $this->assertDatabaseHas('domaines', ['name' => 'Cybersécurité']);
    }

    public function test_creating_a_domain_is_recorded_in_the_audit_journal(): void
    {
        $admin = $this->makeUser('administrateur');

        $this->actingAs($admin)->post(route('admin.domaines.store'), ['name' => 'Données'])->assertRedirect();

        $this->assertDatabaseHas('journal_actions', [
            'user_id' => $admin->id,
            'action' => 'creation',
            'subject_type' => Domaine::class,
        ]);
        $this->assertSame(1, JournalAction::where('subject_type', Domaine::class)->count());
    }

    public function test_non_administrators_cannot_manage_domains_or_see_the_link(): void
    {
        foreach (['directeur', 'chef_service', 'agent', 'lecteur'] as $role) {
            $user = $this->makeUser($role);

            $this->actingAs($user)->get(route('admin.domaines.index'))->assertForbidden();
            $this->actingAs($user)->post(route('admin.domaines.store'), ['name' => 'Interdit '.$role])->assertForbidden();
            $this->actingAs($user)->get(route('dashboard'))->assertDontSee(route('admin.domaines.index'), false);
        }

        $this->assertDatabaseCount('domaines', 0);
    }

    public function test_domain_names_must_be_unique(): void
    {
        $admin = $this->makeUser('administrateur');
        Domaine::create(['name' => 'Réseaux']);

        $this->actingAs($admin)->post(route('admin.domaines.store'), ['name' => 'Réseaux'])->assertSessionHasErrors('name');
    }

    private function makeUser(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], ['label' => ucfirst($roleName)]);
        $service = Service::firstOrCreate(['name' => 'Service test']);

        return User::factory()->create(['role_id' => $role->id, 'service_id' => $service->id, 'is_active' => true]);
    }
}
