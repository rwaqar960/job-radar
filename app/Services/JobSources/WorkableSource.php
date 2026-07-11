<?php

namespace App\Services\JobSources;

use Illuminate\Support\Facades\Http;

class WorkableSource implements JobSource
{
    public function key(): string
    {
        return 'workable';
    }

    public function fetch(): array
    {
        $companies = config('job_sources.ats_companies.workable', []);
        $jobs = [];

        foreach ($companies as $slug) {
            $response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get("https://apply.workable.com/api/v1/widget/accounts/{$slug}");

            if (!$response->ok()) {
                continue;
            }

            $companyName = $response->json('name') ?? $slug;

            foreach ($response->json('jobs', []) as $job) {
                $shortcode = $job['shortcode'] ?? null;
                $location = $job['location'] ?? [];

                $jobs[] = [
                    'external_id' => $shortcode ?? (string) $job['id'],
                    'title' => $job['title'],
                    'company' => $companyName,
                    'location' => $location['location_str'] ?? $location['city'] ?? null,
                    'is_remote' => (bool) ($location['telecommuting'] ?? false),
                    'url' => $job['url'] ?? "https://apply.workable.com/{$slug}/j/{$shortcode}/",
                    'description' => null,
                    'posted_at' => $job['published_on'] ?? null,
                ];
            }
        }

        return $jobs;
    }
}
