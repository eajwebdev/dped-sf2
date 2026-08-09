<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Services\SchoolMasterlistImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class SchoolMasterlistImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_masterlist_command_prepares_for_import_sheet_and_imports_idempotently(): void
    {
        $copy = storage_path('app/testing-school-masterlist.xls');
        copy(public_path('masterlist_of_schools_based_on_school_year_-_original.xls'), $copy);

        $this->artisan('schools:import-masterlist', [
            'file' => $copy,
            '--refresh-import-sheet' => true,
        ])
            ->assertSuccessful();

        $reader = IOFactory::createReaderForFile($copy);
        $reader->setReadDataOnly(true);
        $sheet = $reader->load($copy)->getSheetByName('FOR IMPORT');

        $this->assertNotNull($sheet);
        $this->assertSame(SchoolMasterlistImportService::IMPORT_HEADERS, $sheet->rangeToArray('A1:Y1', null, true, false)[0]);
        $this->assertSame('303207', (string) $sheet->getCell('A2')->getValue());
        $this->assertSame('Amlan NHS', $sheet->getCell('B2')->getValue());

        $this->assertDatabaseHas('schools', [
            'school_id' => '303207',
            'name' => 'Amlan NHS',
            'short_name' => 'ANHS',
            'mother_school_school_id' => '303207',
            'source_school_year' => '2013 - 2014',
            'region' => 'Region VII',
            'province' => 'NEGROS ORIENTAL',
            'municipality' => 'AMLAN (AYUQUITAN)',
            'division' => 'Negros Oriental',
            'district' => 'Amlan',
            'school_head' => 'Rowena Z. Trofeo',
            'school_head_designation' => 'Teacher 3/Officer-In-Charge',
            'telephone_number' => '9276391781',
            'date_of_operation' => '1972-01-01',
            'sub_classification' => 'DepED Managed',
            'curricular_class' => 'Secondary',
            'school_type' => 'Mother school',
            'class_organization' => 'Monograde',
            'education_level' => 'jhs',
            'is_active' => true,
        ]);

        $count = School::count();

        $this->artisan('schools:import-masterlist', ['file' => $copy])
            ->assertSuccessful();

        $this->assertSame($count, School::count());
    }

    public function test_admin_can_import_default_school_masterlist_from_schools_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $this->actingAs($admin)
            ->post(route('admin.schools.import-masterlist'), ['sheet' => 'Worksheet'])
            ->assertRedirect(route('admin.schools.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('schools', [
            'school_id' => '312947',
            'name' => 'Zamboanguita Science HS',
            'district' => 'Zamboanguita',
            'email' => 'zamboanguitascihs@yahoo.com / melissaadanza@gmail.com',
        ]);
    }
}
