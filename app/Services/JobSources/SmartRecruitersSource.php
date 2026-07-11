<?php

namespace App\Services\JobSources;

use Illuminate\Support\Facades\Http;

class SmartRecruitersSource implements JobSource
{
    public function key(): string
    {
        return 'smartrecruiters';
    }

    public function fetch(): array
    {
        $companies = config('job_sources.ats_companies.smartrecruiters', []);
        $jobs = [];

        foreach ($companies as $slug) {
            $response = Http::get("https://api.smartrecruiters.com/v1/companies/{$slug}/postings");

            if (!$response->ok()) {
                continue;
            }

            foreach ($response->json('content', []) as $job) {
                $location = $job['location'] ?? [];

                $jobs[] = [
                    'external_id' => (string) $job['id'],
                    'title' => $job['name'],
                    'company' => $job['company']['name'] ?? $slug,
                    'location' => $location['fullLocation'] ?? null,
                    'is_remote' => (bool) ($location['remote'] ?? false),
                    // The bare id resolves via redirect, no SEO slug needed.
                    'url' => "https://jobs.smartrecruiters.com/{$slug}/{$job['id']}",
                    'description' => null,
                    'posted_at' => $job['releasedDate'] ?? null,
                ];
            }
        }

        return $jobs;
    }
}
