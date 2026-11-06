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

class ActivityOverdueNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_schedule_notifies_the_responsable_once_when_an_activity_becomes_overdue(): void
    {
        Notification::fake([ActivityUpdateNotification::class]);
        $this->travelTo(now()->setTime(8, 0));
        $responsable = $this->makeUser();
        $domain = Domaine::create(['name' => 'Infrastructure']);
        $activity = Activite::create([
            'title' => 'Overdue activity',
            'type' => 'projet',
            'start_date' => today()->subDays(10),
            'due_date' => today()->subDay(),
            'status' => 'en_cours',
            'priority' => 'moyenne',
            'domaine_id' => $domain->id,
            'responsable_id' => $responsable->id,
        ]);

        $this->artisan('schedule:run')->assertExitCode(0);

        Notification::assertSentTo($responsable, ActivityUpdateNotification::class);
        $this->assertNotNull($activity->fresh()->late_notified_at);
    }

    private function makeUser(): User
    {
        $role = Role::firstOrCreate(['name' => 'chef_service'], ['label' => 'Chef de service']);
        $service = Service::firstOrCreate(['name' => 'Service de test']);

        return User::factory()->create(['role_id' => $role->id, 'service_id' => $service->id]);
    }
}
