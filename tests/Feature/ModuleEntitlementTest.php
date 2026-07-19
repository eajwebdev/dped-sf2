<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\SubscriptionPlans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * SF3 and SF5 ship with the Professional plan. The gate only bites paying
 * Starter subscribers — trials, comped and managed accounts see everything.
 */
class ModuleEntitlementTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(array $attributes = []): User
    {
        return User::factory()->create($attributes + [
            'role' => User::ROLE_TEACHER,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
        ]);
    }

    public function test_plan_coverage_matrix(): void
    {
        $this->assertFalse(SubscriptionPlans::planCovers(SubscriptionPlans::STARTER, 'sf3'));
        $this->assertFalse(SubscriptionPlans::planCovers(SubscriptionPlans::STARTER, 'sf5'));
        $this->assertTrue(SubscriptionPlans::planCovers(SubscriptionPlans::PROFESSIONAL, 'sf3'));
        $this->assertTrue(SubscriptionPlans::planCovers(SubscriptionPlans::PROFESSIONAL, 'sf5'));
        $this->assertTrue(SubscriptionPlans::planCovers(SubscriptionPlans::ENTERPRISE, 'sf3'));
        // Ungated modules are open to every plan.
        $this->assertTrue(SubscriptionPlans::planCovers(SubscriptionPlans::STARTER, 'sf1'));
        $this->assertTrue(SubscriptionPlans::planCovers(SubscriptionPlans::STARTER, 'sf2'));
    }

    public function test_a_starter_subscriber_is_sent_to_upgrade_not_served(): void
    {
        $starter = $this->teacher([
            'subscription_plan' => SubscriptionPlans::STARTER,
            'subscribed_until' => Carbon::today()->addMonths(3),
        ]);

        foreach (['reports.sf3.index', 'reports.sf5.index'] as $route) {
            $this->actingAs($starter)->get(route($route))
                ->assertRedirect(route('subscribe.show'))
                ->assertSessionHas('error');
        }
    }

    public function test_a_professional_subscriber_uses_both_modules(): void
    {
        $professional = $this->teacher([
            'subscription_plan' => SubscriptionPlans::PROFESSIONAL,
            'subscribed_until' => Carbon::today()->addMonths(3),
        ]);

        $this->actingAs($professional)->get(route('reports.sf3.index'))->assertOk();
        $this->actingAs($professional)->get(route('reports.sf5.index'))->assertOk();
    }

    public function test_an_enterprise_subscriber_uses_both_modules(): void
    {
        $enterprise = $this->teacher([
            'subscription_plan' => SubscriptionPlans::ENTERPRISE,
            'subscribed_until' => Carbon::today()->addMonths(3),
        ]);

        $this->actingAs($enterprise)->get(route('reports.sf3.index'))->assertOk();
        $this->actingAs($enterprise)->get(route('reports.sf5.index'))->assertOk();
    }

    public function test_a_trial_teacher_sees_the_full_product(): void
    {
        $trial = $this->teacher(['trial_ends_at' => now()->addDays(10)]);

        $this->actingAs($trial)->get(route('reports.sf3.index'))->assertOk();
        $this->actingAs($trial)->get(route('reports.sf5.index'))->assertOk();
    }

    public function test_comped_and_managed_accounts_are_not_gated(): void
    {
        // free_access comp on a Starter plan still passes.
        $comped = $this->teacher([
            'free_access' => true,
            'subscription_plan' => SubscriptionPlans::STARTER,
            'subscribed_until' => Carbon::today()->addMonths(3),
        ]);
        // Managed: admin-provisioned, never entered billing.
        $managed = $this->teacher();

        $this->actingAs($comped)->get(route('reports.sf3.index'))->assertOk();
        $this->actingAs($managed)->get(route('reports.sf5.index'))->assertOk();
    }

    public function test_the_gate_covers_the_sf3_write_routes_too(): void
    {
        $starter = $this->teacher([
            'subscription_plan' => SubscriptionPlans::STARTER,
            'subscribed_until' => Carbon::today()->addMonths(3),
        ]);

        // Route-model binding needs a real section; the gate must fire first.
        $section = \App\Models\Section::factory()->create([
            'school_year_id' => \App\Models\SchoolYear::factory()->active()->create()->id,
            'grade_level_id' => \App\Models\GradeLevel::factory()->create()->id,
        ]);

        $this->actingAs($starter)
            ->post(route('books.store', $section), ['subject_area' => 'X', 'title' => 'Y'])
            ->assertRedirect(route('subscribe.show'));

        $this->assertDatabaseCount('textbooks', 0);
    }
}
