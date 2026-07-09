<?php

namespace App\Services\JobSources;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AshbySource implements JobSource
{
    public function key(): string
    {
        return 'ashby';
    }

    public function fetch(): array
    {
        $companies = config('job_sources.ats_companies.ashby', []);
        $jobs = [];

        foreach ($companies as $slug) {
            $response = Http::get("https://api.ashbyhq.com/posting-api/job-board/{$slug}");

            if (!$response->ok()) {
                continue;
            }

            foreach ($response->json('jobs', []) as $job) {
                $jobs[] = [
                    'external_id' => $job['id'],
                    'title' => $job['title'],
                    'company' => Str::title(str_replace('-', ' ', $slug)),
                    'location' => $job['location'] ?? null,
                    'is_remote' => (bool) ($job['isRemote'] ?? false),
                    'url' => $job['jobUrl'] ?? $job['applyUrl'],
                    'description' => $job['descriptionHtml'] ?? $job['descriptionPlain'] ?? null,
                    'posted_at' => $job['publishedAt'] ?? null,
                ];
            }
        }

        return $jobs;
    }
}
