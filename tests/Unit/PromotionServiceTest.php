<?php

namespace Tests\Unit;

use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Services\PromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionServiceTest extends TestCase
{
    use RefreshDatabase;

    private function enrollment(SchoolYear $year, GradeLevel $grade, Section $section, string $promotion = 'pending'): StudentEnrollment
    {
        return StudentEnrollment::create([
            'student_id' => Student::factory()->create()->id,
            'school_year_id' => $year->id, 'grade_level_id' => $grade->id, 'section_id' => $section->id,
            'status' => 'enrolled', 'promotion_status' => $promotion, 'enrollment_date' => $year->start_date,
        ]);
    }

    public function test_retained_learners_are_skipped(): void
    {
        $from = SchoolYear::factory()->active()->create();
        $to = SchoolYear::factory()->create(['start_date' => '2027-06-01', 'end_date' => '2028-03-31']);
        $g7 = GradeLevel::factory()->create(['level_order' => 7, 'code' => 'G7']);
        $g8 = GradeLevel::factory()->create(['level_order' => 8, 'code' => 'G8']);
        $src = Section::factory()->create(['school_year_id' => $from->id, 'grade_level_id' => $g7->id]);
        $dst = Section::factory()->create(['school_year_id' => $to->id, 'grade_level_id' => $g8->id]);

        $this->enrollment($from, $g7, $src, promotion: 'retained');

        $result = app(PromotionService::class)->promote($from, $to, [$src->id => $dst->id], User::factory()->create(['role' => 'admin']));

        $this->assertSame(1, $result['retained']);
        $this->assertSame(0, $result['promoted']);
        $this->assertSame(0, StudentEnrollment::where('school_year_id', $to->id)->count());
    }

    public function test_unmapped_section_is_skipped(): void
    {
        $from = SchoolYear::factory()->active()->create();
        $to = SchoolYear::factory()->create(['start_date' => '2027-06-01', 'end_date' => '2028-03-31']);
        $g7 = GradeLevel::factory()->create(['level_order' => 7, 'code' => 'G7']);
        GradeLevel::factory()->create(['level_order' => 8, 'code' => 'G8']);
        $src = Section::factory()->create(['school_year_id' => $from->id, 'grade_level_id' => $g7->id]);

        $this->enrollment($from, $g7, $src);

        // No mapping provided → skipped, no target enrollment created.
        $result = app(PromotionService::class)->promote($from, $to, [], User::factory()->create(['role' => 'admin']));

        $this->assertSame(1, $result['skipped']);
        $this->assertSame(0, StudentEnrollment::where('school_year_id', $to->id)->count());
    }
}
