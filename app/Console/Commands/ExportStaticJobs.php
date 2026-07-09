<?php

namespace App\Console\Commands;

use App\Models\JobPosting;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('jobs:export-static')]
#[Description('Export tagged job postings to docs/data/jobs.json for the static GitHub Pages frontend.')]
class ExportStaticJobs extends Command
{
    public function handle(): int
    {
        $jobs = JobPosting::query()
            ->orderByDesc('posted_at')
            ->get()
            ->map(fn (JobPosting $job) => [
                'id' => $job->id,
                'source' => $job->source,
                'title' => $job->title,
                'company' => $job->company,
                'location' => $job->location,
                'is_remote' => $job->is_remote,
                'url' => $job->url,
                'tags' => $job->tags ?? [],
                'posted_at' => $job->posted_at?->toIso8601String(),
            ]);

        $payload = [
            'generatedAt' => Carbon::now()->toIso8601String(),
            'stacks' => array_keys(config('job_sources.stacks', [])),
            'jobs' => $jobs,
        ];

        $path = base_path('docs/data/jobs.json');

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info('Exported '.count($jobs)." postings to {$path}");

        return self::SUCCESS;
    }
}
