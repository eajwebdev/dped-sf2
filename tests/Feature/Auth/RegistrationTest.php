<?php

namespace Tests\Feature\Auth;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        School::factory()->create();

        $this->get('/register')->assertOk();
    }

    public function test_a_teacher_can_self_register_into_a_school_as_pending(): void
    {
        $school = School::factory()->create();

        $response = $this->post('/register', [
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'contact_number' => '09171234567',
            'school_id' => $school->id,
            'password' => 'password',
            'password_confirmation' => 'password',
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
