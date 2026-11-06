<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RapportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_report_download_contains_a_pdf_document(): void
    {
        $director = $this->makeDirector();

        $response = $this->actingAs($director)->get(route('rapports.pdf'));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_excel_report_download_is_a_valid_xlsx_archive(): void
    {
        $director = $this->makeDirector();

        $response = $this->actingAs($director)->get(route('rapports.excel'));

        $response->assertDownload('rapport-activites-'.now()->format('Y-m-d').'.xlsx');
        $archive = new \ZipArchive;
        $this->assertTrue($archive->open($response->baseResponse->getFile()->getPathname()) === true);
        $this->assertNotFalse($archive->locateName('xl/worksheets/sheet1.xml'));
        $archive->close();
    }

    private function makeDirector(): User
    {
        $role = Role::firstOrCreate(['name' => 'directeur'], ['label' => 'Directeur']);
        $service = Service::firstOrCreate(['name' => 'Service de direction']);

        return User::factory()->create(['role_id' => $role->id, 'service_id' => $service->id]);
    }
}
