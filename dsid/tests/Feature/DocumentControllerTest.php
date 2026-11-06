<?php

namespace Tests\Feature;

use App\Models\Activite;
use App\Models\Document;
use App\Models\Domaine;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_contributing_agent_can_upload_a_document_to_a_private_disk(): void
    {
        Storage::fake('local');
        $chef = $this->makeUser('chef_service', 'Chef');
        $agent = $this->makeUser('agent', 'Agent');
        $activity = $this->makeActivity($chef);
        $activity->taches()->create(['title' => 'Rédiger', 'status' => 'a_demarrer', 'progress' => 0, 'agent_id' => $agent->id]);

        $this->actingAs($agent)->post(route('activites.documents.store', $activity), [
            'document' => UploadedFile::fake()->create('compte-rendu.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $document = Document::firstOrFail();
        $this->assertSame('compte-rendu.pdf', $document->original_name);
        $this->assertSame($agent->id, $document->uploaded_by);
        Storage::disk('local')->assertExists($document->storage_path);
    }

    public function test_a_reader_cannot_upload_a_document(): void
    {
        Storage::fake('local');
        $chef = $this->makeUser('chef_service', 'Chef');
        $lecteur = $this->makeUser('lecteur', 'Lecteur');
        $activity = $this->makeActivity($chef);

        $this->actingAs($lecteur)->post(route('activites.documents.store', $activity), [
            'document' => UploadedFile::fake()->create('note.pdf', 10, 'application/pdf'),
        ])->assertForbidden();

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_executable_files_are_rejected(): void
    {
        Storage::fake('local');
        $chef = $this->makeUser('chef_service', 'Chef');
        $activity = $this->makeActivity($chef);

        $this->actingAs($chef)->post(route('activites.documents.store', $activity), [
            'document' => UploadedFile::fake()->create('script.php', 5, 'application/x-php'),
        ])->assertSessionHasErrors('document');

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_an_agent_unrelated_to_the_activity_cannot_download_its_documents(): void
    {
        Storage::fake('local');
        $chef = $this->makeUser('chef_service', 'Chef');
        $outsider = $this->makeUser('agent', 'Autre');
        $activity = $this->makeActivity($chef);
        $path = UploadedFile::fake()->create('rapport.pdf', 10, 'application/pdf')->store('documents/'.$activity->id, 'local');
        $document = $activity->documents()->create([
            'original_name' => 'rapport.pdf', 'storage_path' => $path, 'mime_type' => 'application/pdf',
            'size_bytes' => 10240, 'uploaded_by' => $chef->id,
        ]);

        $this->actingAs($outsider)->get(route('documents.download', $document))->assertForbidden();
        $this->actingAs($chef)->get(route('documents.download', $document))->assertOk();
    }

    private function makeUser(string $roleName, string $name): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], ['label' => $name]);
        $service = Service::firstOrCreate(['name' => 'Service '.$name]);

        return User::factory()->create(['role_id' => $role->id, 'service_id' => $service->id, 'is_active' => true]);
    }

    private function makeActivity(User $responsable): Activite
    {
        return Activite::create([
            'title' => 'Schéma directeur',
            'type' => 'projet',
            'start_date' => today()->subDay(),
            'due_date' => today()->addDays(30),
            'status' => 'a_demarrer',
            'priority' => 'moyenne',
            'domaine_id' => Domaine::firstOrCreate(['name' => 'Documentation'])->id,
            'responsable_id' => $responsable->id,
        ]);
    }
}
