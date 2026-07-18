<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_hero_carries_the_asfs_positioning(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('EAJ ASFS')
            ->assertSee('DepEd-Compliant Automated School Forms System')
            ->assertSee('One Platform.')
            ->assertSee('Every DepEd School Form.')
            // The strongest marketing line must survive the rebrand.
            ->assertSee('Attendance in seconds.')
            ->assertSee('in one click.', false)
            ->assertSee('Start with SF1 and SF2 today and seamlessly expand');
    }

    public function test_every_adviser_module_is_listed_with_a_status(): void
    {
        $response = $this->get('/')->assertOk();

        foreach (['SF1', 'SF2', 'SF3', 'SF5', 'SF8', 'SF9', 'SF10'] as $code) {
            $response->assertSee($code);
        }

        $response->assertSee('School Form Modules')
            ->assertSee('Available')
            ->assertSee('Coming soon')
            ->assertSee('In development — not yet included', false)
            // SF1 and SF2 ship today; the other five must still read as upcoming.
            ->assertSee('School Forms 1 and 2 are included today');
    }

    public function test_pricing_shows_three_priced_tiers_and_the_advance_discount(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Starter Plan')
            ->assertSee('Professional Plan')
            ->assertSee('Enterprise Plan')
            // Prices must match what checkout will actually charge.
            ->assertSee('₱199')
            ->assertSee('₱269')
            ->assertSee('₱449')
            ->assertSee('Pay ahead and save')
            // Perks that depend on unshipped modules stay clearly marked.
            ->assertSee('On release');
    }

    public function test_no_dead_anchors_remain_from_the_old_layout(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('#coming-soon', $html);
        $this->assertStringContainsString('id="features"', $html);
        $this->assertStringContainsString('id="capabilities"', $html);
    }
}
