<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_has_search_metadata_for_school_forms(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<meta name="description"', false)
            ->assertSee('DepEd School Forms', false)
            ->assertSee('SF1', false)
            ->assertSee('SF10', false)
            ->assertSee('<link rel="canonical" href="https://eaj-sf.com/">', false)
            ->assertSee('application/ld+json', false);
    }

    public function test_school_forms_index_and_specific_pages_are_indexable(): void
    {
        $this->get('/school-forms')
            ->assertOk()
            ->assertSee('School Forms SF1 to SF10 automation')
            ->assertSee('<link rel="canonical" href="https://eaj-sf.com/school-forms">', false);

        foreach (['sf1', 'sf2', 'sf3', 'sf4', 'sf5', 'sf6', 'sf7', 'sf8', 'sf9', 'sf10'] as $form) {
            $this->get("/school-forms/{$form}")
                ->assertOk()
                ->assertSee(strtoupper($form))
                ->assertSee('<meta name="robots" content="index, follow">', false)
                ->assertSee("https://eaj-sf.com/school-forms/{$form}", false);
        }
    }

    public function test_sitemap_and_robots_expose_public_school_form_pages(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('content-type', 'application/xml')
            ->assertSee('https://eaj-sf.com/', false)
            ->assertSee('https://eaj-sf.com/school-forms/sf1', false)
            ->assertSee('https://eaj-sf.com/school-forms/sf10', false);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap: https://eaj-sf.com/sitemap.xml');
    }
}
