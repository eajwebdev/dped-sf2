<?php

namespace Tests\Feature\Auth;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        School::factory()->create();

        $this->get('/register')
            ->assertOk()
            ->assertDontSee('No schools available yet');
    }

    public function test_registration_school_search_returns_limited_active_matches(): void
    {
        $target = School::factory()->create([
            'school_id' => '303246',
            'name' => 'Dahile National High School',
            'division' => 'Negros Oriental',
            'region' => 'Region VII',
            'is_active' => true,
        ]);

        $inactive = School::factory()->inactive()->create([
            'school_id' => '303247',
            'name' => 'Dahile Closed School',
        ]);

        for ($i = 1; $i <= 25; $i++) {
            School::factory()->create([
                'school_id' => (string) (800000 + $i),
                'name' => 'Dahile Match '.$i,
                'is_active' => true,
            ]);
        }

        $response = $this->getJson(route('register.schools', ['q' => 'Dahile']));

        $response->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonMissing(['id' => $inactive->id]);

        $this->getJson(route('register.schools', ['q' => '303246']))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $target->id,
                'label' => 'Dahile National High School (ID 303246)',
                'meta' => 'Negros Oriental • Region VII',
            ]);

        $this->getJson(route('register.schools', ['q' => '303247']))
            ->assertOk()
            ->assertJsonMissing(['id' => $inactive->id]);
    }

    public function test_registration_school_search_requires_at_least_two_characters(): void
    {
        School::factory()->create(['name' => 'Dahile National High School']);

        $this->getJson(route('register.schools', ['q' => 'D']))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_a_teacher_can_self_register_into_a_school_as_pending(): void
    {
        Storage::fake('local');
        $school = School::factory()->create();

        $response = $this->post('/register', [
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'contact_number' => '09171234567',
            'school_id' => $school->id,
            // Registration now requires proof the applicant works at this school.
            'school_id_number' => '2019-04821',
            'school_id_document' => UploadedFile::fake()->image('id.jpg', 800, 600)->size(500),
            'password' => 'Str0ng-Passw0rd!',
            'password_confirmation' => 'Str0ng-Passw0rd!',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('account.pending'));

        $this->assertDatabaseHas('users', [
            'email' => 'maria@example.com',
            'role' => User::ROLE_TEACHER,
            'status' => User::STATUS_PENDING,
            'school_id' => $school->id,
        ]);
    }

    public function test_registration_requires_a_school(): void
    {
        $this->post('/register', [
            'name' => 'No School',
            'email' => 'noschool@example.com',
            'contact_number' => '09171234567',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('school_id');

        $this->assertDatabaseMissing('users', ['email' => 'noschool@example.com']);
    }

    public function test_registration_rejects_inactive_schools(): void
    {
        Storage::fake('local');
        $school = School::factory()->inactive()->create();

        $this->post('/register', [
            'name' => 'Inactive School',
            'email' => 'inactive@example.com',
            'contact_number' => '09171234567',
            'school_id' => $school->id,
            'school_id_number' => '2019-04821',
            'school_id_document' => UploadedFile::fake()->image('id.jpg', 800, 600)->size(500),
            'password' => 'Str0ng-Passw0rd!',
            'password_confirmation' => 'Str0ng-Passw0rd!',
        ])->assertSessionHasErrors('school_id');

        $this->assertDatabaseMissing('users', ['email' => 'inactive@example.com']);
    }

    public function test_a_pending_teacher_cannot_reach_the_teacher_app(): void
    {
        $school = School::factory()->create();
        $pending = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => User::STATUS_PENDING,
            'school_id' => $school->id,
        ]);

        $this->actingAs($pending)->get(route('teacher.dashboard'))
            ->assertRedirect(route('account.pending'));
    }
}
