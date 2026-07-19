<?php

namespace Tests;

use App\Models\School;
use App\Models\Setting;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /*
         * Setting keeps resolved values in a static for the life of the PHP
         * process. That is what makes a request cheap, but PHPUnit runs every
         * test in one process — so without this a value written by one test
         * would still be visible after the database has been rolled back,
         * producing passes and failures that depend on test order.
         */
        Setting::flushMemo();
        School::forgetSoleId();
    }

    protected function tearDown(): void
    {
        Setting::flushMemo();
        School::forgetSoleId();

        parent::tearDown();
    }
}
