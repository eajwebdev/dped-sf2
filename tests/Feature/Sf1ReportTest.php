<?php

namespace Tests\Feature;

use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Sf1ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class Sf1ReportTest extends TestCase
{
    use RefreshDatabase;

    private SchoolYear $year;

    private Section $section;

    private User $adviser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->year = SchoolYear::factory()->active()->create([
            'start_date' => '2025-06-01', 'end_date' => '2026-03-31',
        ]);
        $grade = GradeLevel::factory()->create(['level_order' => 7, 'code' => 'G7']);

        $this->adviser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::factory()->create(['user_id' => $this->adviser->id]);

        $this->section = Section::factory()->create([
            'school_year_id' => $this->year->id,
            'grade_level_id' => $grade->id,
            'adviser_id' => $teacher->id,
        ]);
    }

    private function enroll(string $gender, array $student = [], array $enrollment = []): StudentEnrollment
    {
        // Suffix pinned off by default: the factory adds "Jr."/"III" 5% of the
        // time, and these tests identify learners by the last-name segment of
        // the printed register name — which a suffix silently becomes part of.
        $s = Student::factory()->create($student + ['gender' => $gender, 'suffix' => null]);

        return StudentEnrollment::create($enrollment + [
            'student_id' => $s->id,
            'school_year_id' => $this->year->id,
            'grade_level_id' => $this->section->grade_level_id,
            'section_id' => $this->section->id,
            'status' => 'enrolled',
            'promotion_status' => 'pending',
            'enrollment_date' => '2025-06-01',
        ]);
    }

    public function test_age_cut_off_is_the_first_friday_of_june(): void
    {
        $cutOff = app(Sf1ReportService::class)->firstFridayOfJune(Carbon::parse('2025-06-01'));

        // 6 June 2025 is the first Friday of June that year.
        $this->assertSame('2025-06-06', $cutOff->toDateString());
        $this->assertSame('Friday', $cutOff->format('l'));
    }

    public function test_learners_are_split_by_sex_and_sorted_a_to_z(): void
    {
        $this->enroll('Male', ['last_name' => 'Zamora', 'first_name' => 'Ben']);
        $this->enroll('Male', ['last_name' => 'Abad', 'first_name' => 'Ana']);
        $this->enroll('Female', ['last_name' => 'Reyes', 'first_name' => 'Cara']);
        $this->enroll('Female', ['last_name' => 'Cruz', 'first_name' => 'Dina']);

        $data = app(Sf1ReportService::class)->build($this->section);

        $this->assertCount(2, $data['males']);
        $this->assertCount(2, $data['females']);
        $this->assertStringStartsWith('Abad', $data['males'][0]['name']);
        $this->assertStringStartsWith('Zamora', $data['males'][1]['name']);
        $this->assertStringStartsWith('Cruz', $data['females'][0]['name']);
        $this->assertStringStartsWith('Reyes', $data['females'][1]['name']);

        // Numbering restarts per sex, matching the register's grouped layout.
        $this->assertSame(1, $data['males'][0]['no']);
        $this->assertSame(1, $data['females'][0]['no']);
    }

    public function test_age_is_computed_against_the_cut_off(): void
    {
        $this->enroll('Male', ['birthdate' => '2013-06-01']);   // turns 12 before the cut-off
        $this->enroll('Male', ['birthdate' => '2013-08-01']);   // still 11 on the cut-off

        $data = app(Sf1ReportService::class)->build($this->section);
        $ages = collect($data['males'])->pluck('age')->sort()->values()->all();

        $this->assertSame([11, 12], $ages);
    }

    public function test_remark_codes_are_derived_from_enrolment_state(): void
    {
        $this->enroll('Male', ['last_name' => 'Aquino'], [
            'status' => 'transferred_out',
            'transfer_school' => 'Sto. Nino ES (P)',
            'transfer_date' => '2025-09-15',
        ]);
        $this->enroll('Male', ['last_name' => 'Bautista'], [
            'is_late_enrollment' => true,
            'late_enrollment_reason' => 'Family relocated',
        ]);
        $this->enroll('Male', ['last_name' => 'Castro'], [
            'cct_reference' => '12345 08/01/2025',
            'disability_detail' => 'Visual impairment',
        ]);

        $rows = collect(app(Sf1ReportService::class)->build($this->section)['males'])->keyBy(
            fn ($r) => explode(',', $r['name'])[0]
        );

        $this->assertSame('T/O Sto. Nino ES (P) 09/15/2025', $rows['Aquino']['remarks']);
        $this->assertSame('LE Family relocated', $rows['Bautista']['remarks']);
        $this->assertSame('CCT 12345 08/01/2025; LWD Visual impairment', $rows['Castro']['remarks']);
    }

    public function test_bosy_and_eosy_registration_counts(): void
    {
        $this->enroll('Male');                                              // BoSY + EoSY
        $this->enroll('Male', [], ['status' => 'transferred_out']);         // BoSY only
        $this->enroll('Female');                                            // BoSY + EoSY
        $this->enroll('Female', [], [                                       // EoSY only (late, transferred in)
            'status' => 'transferred_in', 'is_late_enrollment' => true,
        ]);

        $sum = app(Sf1ReportService::class)->build($this->section)['summary'];

        $this->assertSame(['bosy' => 2, 'eosy' => 1], $sum['male']);
        $this->assertSame(['bosy' => 1, 'eosy' => 2], $sum['female']);
        $this->assertSame(['bosy' => 3, 'eosy' => 3], $sum['total']);
    }

    public function test_adviser_can_generate_the_sf1_pdf(): void
    {
        $this->enroll('Male', [
            'last_name' => 'Dela Cruz', 'first_name' => 'Juan', 'middle_name' => 'Santos',
            'birthdate' => '2013-04-10', 'birth_place' => 'Bulacan',
            'mother_tongue' => 'Tagalog', 'religion' => 'Roman Catholic',
            'address_street' => '12 Mabini St.', 'address_barangay' => 'San Jose',
            'address_municipality' => 'Malolos', 'address_province' => 'Bulacan',
            'father_name' => 'Pedro', 'mother_name' => 'Maria Santos Reyes',
            'guardian_name' => 'Ana Reyes', 'guardian_relationship' => 'Aunt',
            'guardian_contact' => '09171234567',
        ]);
        $this->enroll('Female', ['last_name' => 'Santos', 'first_name' => 'Maria']);

        $response = $this->actingAs($this->adviser)
            ->get(route('reports.sf1.show', $this->section).'?head=JUAN+A.+DELA+CRUZ');

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_sf1_profile_fields_are_captured_through_the_student_form(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $this->actingAs($admin)->post(route('admin.students.store'), [
            'lrn' => '123456789012',
            'first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'gender' => 'Male',
            'status' => 'active', 'birthdate' => '2013-04-10',
            'birth_place' => 'Bulacan', 'mother_tongue' => 'Tagalog',
            'ethnic_group' => 'Aeta', 'religion' => 'Roman Catholic',
            'address_street' => '12 Mabini St.', 'address_barangay' => 'San Jose',
            'address_municipality' => 'Malolos', 'address_province' => 'Bulacan',
            'father_name' => 'Pedro', 'mother_name' => 'Maria Santos Reyes',
            'guardian_name' => 'Ana Reyes', 'guardian_relationship' => 'Aunt',
            'guardian_contact' => '09171234567',
        ])->assertRedirect();

        $this->assertDatabaseHas('students', [
            'lrn' => '123456789012',
            'birth_place' => 'Bulacan',
            'mother_tongue' => 'Tagalog',
            'ethnic_group' => 'Aeta',
            'religion' => 'Roman Catholic',
            'address_barangay' => 'San Jose',
            'father_name' => 'Pedro',
            'mother_name' => 'Maria Santos Reyes',
            'guardian_relationship' => 'Aunt',
        ]);
    }

    /**
     * The picker offers every school year the teacher advised in, and a
     * past-year section still generates — tracking previous years is the
     * whole point of the SY selector.
     */
    public function test_previous_school_years_stay_listed_and_generate(): void
    {
        $oldYear = SchoolYear::factory()->create([
            'name' => '2024-2025', 'is_active' => false,
            'start_date' => '2024-06-01', 'end_date' => '2025-03-31',
        ]);
        $oldSection = Section::factory()->create([
            'school_year_id' => $oldYear->id,
            'grade_level_id' => $this->section->grade_level_id,
            'adviser_id' => $this->section->adviser_id,
        ]);
        $student = Student::factory()->create(['gender' => 'Male']);
        StudentEnrollment::create([
            'student_id' => $student->id, 'school_year_id' => $oldYear->id,
            'grade_level_id' => $oldSection->grade_level_id, 'section_id' => $oldSection->id,
            'status' => 'enrolled', 'promotion_status' => 'pending', 'enrollment_date' => '2024-06-01',
        ]);

        // Both years appear on the picker.
        $this->actingAs($this->adviser)->get(route('reports.sf1.index'))
            ->assertOk()
            ->assertSee('School Year')
            ->assertSee('2024-2025')
            ->assertSee($this->year->name);

        // And the old year's register still prints.
        $response = $this->actingAs($this->adviser)->get(route('reports.sf1.show', $oldSection));
        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_non_adviser_cannot_generate_the_report(): void
    {
        $other = User::factory()->create(['role' => 'teacher']);
        Teacher::factory()->create(['user_id' => $other->id]);

        $this->actingAs($other)
            ->get(route('reports.sf1.show', $this->section))
            ->assertForbidden();
    }
}
