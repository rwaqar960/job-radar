<?php

return [

    // Tech-stack keyword map used to tag each posting. Matching is done with
    // word-boundary regex against "title + description" (see SyncJobs::tagsFor()).
    'stacks' => [
        'flutter' => ['flutter', 'dart'],
        'android' => ['android', 'kotlin', 'jetpack compose'],
        'php' => ['php', 'laravel', 'symfony', 'wordpress'],
        'dotnet' => ['.net', 'c#', 'asp.net', 'dotnet'],
        'java' => ['java', 'spring boot', 'spring framework'],
    ],

    // Company slugs to poll directly via their ATS public job-board API.
    // Slug = the identifier used in the company's careers page URL
    // (e.g. jobs.ashbyhq.com/{slug}, jobs.lever.co/{slug}, boards.greenhouse.io/{slug}).
    'ats_companies' => [
        'greenhouse' => [
            'stripe',
            'coinbase',
            'robinhood',
            'figma',
            'airbnb',
            'pinterest',
        ],
        'lever' => [
            'nium',
            'plaid',
        ],
        'ashby' => [
            'ramp',
            'linear',
            'notion',
        ],
    ],

];
