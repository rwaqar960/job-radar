<?php

namespace App\Services\JobSources;

use Illuminate\Support\Facades\Http;

class RemoteOkSource implements JobSource
{
    public function key(): string
    {
        return 'remoteok';
    }

    public function fetch(): array
    {
        $response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])
            ->get('https://remoteok.com/api')
            ->throw()
            ->json();

        // First element is a legal-notice object, not a job.
        $jobs = array_slice($response, 1);

        return array_map(fn (array $job) => [
            'external_id' => (string) $job['id'],
            'title' => $job['position'],
            'company' => $job['company'],
            'location' => $job['location'] ?: null,
            'is_remote' => true,
            'url' => $job['apply_url'] ?? $job['url'],
            'description' => $job['description'] ?? null,
            'posted_at' => $job['date'] ?? null,
        ], $jobs);
    }
}
