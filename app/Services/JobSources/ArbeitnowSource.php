<?php

namespace App\Services\JobSources;

use Illuminate\Support\Facades\Http;

class ArbeitnowSource implements JobSource
{
    public function key(): string
    {
        return 'arbeitnow';
    }

    public function fetch(): array
    {
        $jobs = Http::get('https://www.arbeitnow.com/api/job-board-api')
            ->throw()
            ->json('data');

        return array_map(fn (array $job) => [
            'external_id' => $job['slug'],
            'title' => $job['title'],
            'company' => $job['company_name'],
            'location' => $job['location'] ?: null,
            'is_remote' => (bool) $job['remote'],
            'url' => $job['url'],
            'description' => $job['description'] ?? null,
            'posted_at' => isset($job['created_at']) ? date('c', $job['created_at']) : null,
        ], $jobs);
    }
}
