<?php

namespace App\Services\JobSources;

use Illuminate\Support\Facades\Http;

class HimalayasSource implements JobSource
{
    public function key(): string
    {
        return 'himalayas';
    }

    public function fetch(): array
    {
        $jobs = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])
            ->get('https://himalayas.app/jobs/api', ['limit' => 50])
            ->throw()
            ->json('jobs');

        return array_map(fn (array $job) => [
            'external_id' => $job['guid'],
            'title' => $job['title'],
            'company' => $job['companyName'],
            'location' => !empty($job['locationRestrictions']) ? implode(', ', $job['locationRestrictions']) : 'Worldwide',
            'is_remote' => true,
            'url' => $job['applicationLink'],
            'description' => $job['description'] ?? $job['excerpt'] ?? null,
            'posted_at' => $job['pubDate'] ?? null,
        ], $jobs);
    }
}
