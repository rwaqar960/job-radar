<?php

namespace App\Services\JobSources;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LeverSource implements JobSource
{
    public function key(): string
    {
        return 'lever';
    }

    public function fetch(): array
    {
        $companies = config('job_sources.ats_companies.lever', []);
        $jobs = [];

        foreach ($companies as $slug) {
            $response = Http::get("https://api.lever.co/v0/postings/{$slug}", ['mode' => 'json']);

            if (!$response->ok()) {
                continue;
            }

            foreach ($response->json() ?? [] as $job) {
                $jobs[] = [
                    'external_id' => $job['id'],
                    'title' => $job['text'],
                    'company' => Str::title(str_replace('-', ' ', $slug)),
                    'location' => $job['categories']['location'] ?? null,
                    'is_remote' => strtolower($job['workplaceType'] ?? '') === 'remote',
                    'url' => $job['hostedUrl'],
                    'description' => $job['descriptionPlain'] ?? $job['description'] ?? null,
                    'posted_at' => isset($job['createdAt']) ? date('c', intdiv($job['createdAt'], 1000)) : null,
                ];
            }
        }

        return $jobs;
    }
}
