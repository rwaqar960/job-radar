<?php

namespace App\Services\JobSources;

use Illuminate\Support\Facades\Http;

class WorkingNomadsSource implements JobSource
{
    public function key(): string
    {
        return 'workingnomads';
    }

    public function fetch(): array
    {
        $jobs = Http::get('https://www.workingnomads.com/api/exposed_jobs/')
            ->throw()
            ->json();

        return array_map(fn (array $job) => [
            'external_id' => $job['url'],
            'title' => $job['title'],
            'company' => $job['company_name'],
            'location' => $job['location'] ?: null,
            'is_remote' => true,
            'url' => $job['url'],
            'description' => $job['description'] ?? null,
            'posted_at' => $job['pub_date'] ?? null,
        ], $jobs);
    }
}
