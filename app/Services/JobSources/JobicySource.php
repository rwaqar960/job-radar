<?php

namespace App\Services\JobSources;

use Illuminate\Support\Facades\Http;

class JobicySource implements JobSource
{
    public function key(): string
    {
        return 'jobicy';
    }

    public function fetch(): array
    {
        $jobs = Http::get('https://jobicy.com/api/v2/remote-jobs', ['count' => 50])
            ->throw()
            ->json('jobs');

        return array_map(fn (array $job) => [
            'external_id' => (string) $job['id'],
            'title' => $job['jobTitle'],
            'company' => $job['companyName'],
            'location' => $job['jobGeo'] ?? null,
            'is_remote' => true,
            'url' => $job['url'],
            'description' => $job['jobDescription'] ?? $job['jobExcerpt'] ?? null,
            'posted_at' => $job['pubDate'] ?? null,
        ], $jobs);
    }
}
