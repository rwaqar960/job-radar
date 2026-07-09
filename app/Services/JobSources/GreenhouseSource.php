<?php

namespace App\Services\JobSources;

use Illuminate\Support\Facades\Http;

class GreenhouseSource implements JobSource
{
    public function key(): string
    {
        return 'greenhouse';
    }

    public function fetch(): array
    {
        $companies = config('job_sources.ats_companies.greenhouse', []);
        $jobs = [];

        foreach ($companies as $slug) {
            $response = Http::get("https://boards-api.greenhouse.io/v1/boards/{$slug}/jobs", ['content' => 'true']);

            if (!$response->ok()) {
                continue;
            }

            foreach ($response->json('jobs', []) as $job) {
                $jobs[] = [
                    'external_id' => (string) $job['id'],
                    'title' => $job['title'],
                    'company' => $job['company_name'] ?? ucfirst($slug),
                    'location' => $job['location']['name'] ?? null,
                    'is_remote' => str_contains(strtolower($job['location']['name'] ?? ''), 'remote'),
                    'url' => $job['absolute_url'],
                    'description' => $job['content'] ?? null,
                    'posted_at' => $job['first_published'] ?? $job['updated_at'] ?? null,
                ];
            }
        }

        return $jobs;
    }
}
