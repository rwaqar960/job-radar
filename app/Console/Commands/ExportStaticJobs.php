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
    private const MAX_AGE_DAYS = 30;

    public function handle(): int
    {
        $cutoff = Carbon::now()->subDays(self::MAX_AGE_DAYS);

        $jobs = JobPosting::query()
            ->where(function ($q) use ($cutoff) {
                $q->whereNull('posted_at')->orWhere('posted_at', '>=', $cutoff);
            })
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

        $totalInDb = JobPosting::count();
        $this->info('Exported '.count($jobs)." postings to {$path} (".($totalInDb - count($jobs))." older than ".self::MAX_AGE_DAYS." days excluded)");

        return self::SUCCESS;
    }
}
