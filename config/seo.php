<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Public SEO Canonical Domain
    |--------------------------------------------------------------------------
    |
    | Keep this separate from APP_URL so local/dev URLs never leak into
    | canonical tags, OpenGraph URLs, robots.txt, or the sitemap.
    |
    */
    'site_url' => env('SEO_SITE_URL', 'https://eaj-sf.com'),

    'site_name' => env('SEO_SITE_NAME', 'EAJ SF'),

    'default_title' => 'EAJ SF — DepEd School Forms Automation System',

    'default_description' => 'EAJ SF automates DepEd School Forms including SF1, SF2, SF3, SF5, SF8, SF9, and SF10 with QR attendance, student records, and print-ready reports.',

    'keywords' => [
        'DepEd School Forms',
        'School Forms',
        'SF',
        'SF1',
        'SF2',
        'SF3',
        'SF4',
        'SF5',
        'SF6',
        'SF7',
        'SF8',
        'SF9',
        'SF10',
        'School Form 1',
        'School Form 2',
        'School Form 10',
        'DepEd SF1',
        'DepEd SF2',
        'DepEd SF10',
        'Automated School Forms System',
        'teacher attendance system Philippines',
        'DepEd forms automation',
    ],
];
