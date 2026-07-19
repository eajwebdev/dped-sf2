<?php

namespace Tests\Feature;

use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Teacher;
use App\Models\User;
use App\Support\ReportSchool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every School Form prints the DepEd School ID and the school's own seal. The
 * school behind those two facts is resolved once, here — these cover the
 * fallback order, because a section without a school silently produced a blank
 * School ID and the placeholder logo on all five forms.
 */
class ReportSchoolTest extends TestCase
{
    use RefreshDatabase;

    private function section(array $attributes = []): Section
    {
        $year = SchoolYear::factory()->active()->create();
        $grade = GradeLevel::factory()->create(['level_order' => 7, 'code' => 'G7']);

        return Section::factory()->create($attributes + [
            'school_year_id' => $year->id,
            'grade_level_id' => $grade->id,
        ]);
    }

    public function test_the_sections_own_school_wins(): void
    {
        $sectionSchool = School::factory()->create(['school_id' => '111111']);
        $adviserSchool = School::factory()->create(['school_id' => '222222']);

        $teacher = Teacher::factory()->create(['school_id' => $adviserSchool->id]);
        $section = $this->section([
            'school_id' => $sectionSchool->id,
            'adviser_id' => $teacher->id,
        ]);

        $this->assertSame('111111', ReportSchool::for($section)->school_id);
    }

    public function test_it_falls_back_to_the_school_the_adviser_belongs_to(): void
    {
        $school = School::factory()->create(['school_id' => '303246']);
        $teacher = Teacher::factory()->create(['school_id' => $school->id]);

        $section = $this->section(['school_id' => null, 'adviser_id' => $teacher->id]);

        $this->assertSame('303246', ReportSchool::for($section)->school_id);
    }

    public function test_it_falls_back_to_the_advisers_user_account(): void
    {
        $school = School::factory()->create(['school_id' => '404040']);
        $user = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);
        $teacher = Teacher::factory()->create(['user_id' => $user->id, 'school_id' => null]);

        $section = $this->section(['school_id' => null, 'adviser_id' => $teacher->id]);

        $this->assertSame('404040', ReportSchool::for($section)->school_id);
    }

    public function test_an_unlinked_section_resolves_to_null_rather_than_guessing(): void
    {
        $section = $this->section(['school_id' => null, 'adviser_id' => null]);

        $this->assertNull(ReportSchool::for($section));
    }

    public function test_the_schools_own_logo_is_used_when_the_file_exists(): void
    {
        $school = School::factory()->create(['logo_path' => 'logo.png']);

        $this->assertSame(public_path('logo.png'), ReportSchool::logoPath($school));
    }

    public function test_a_missing_logo_file_falls_back_instead_of_breaking_the_pdf(): void
    {
        // A row pointing at a deleted upload must not hand DomPDF a dead path.
        $school = School::factory()->create(['logo_path' => 'school-logos/deleted.png']);

        $this->assertSame(public_path('logo.png'), ReportSchool::logoPath($school));
        $this->assertSame(public_path('logo.png'), ReportSchool::logoPath(null));
    }
}
