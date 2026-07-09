<?php

namespace App\Services\JobSources;

use Illuminate\Support\Facades\Http;

class RemotiveSource implements JobSource
{
    public function key(): string
    {
        return 'remotive';
    }

    public function fetch(): array
    {
        $jobs = Http::get('https://remotive.com/api/remote-jobs')
            ->throw()
            ->json('jobs');

        return array_map(fn (array $job) => [
            'external_id' => (string) $job['id'],
            'title' => $job['title'],
            'company' => $job['company_name'],
            'location' => $job['candidate_required_location'] ?? null,
            'is_remote' => true,
            'url' => $job['url'],
            'description' => $job['description'] ?? null,
            'posted_at' => $job['publication_date'] ?? null,
        ], $jobs);
    }
}
