<?php

namespace App\Console\Commands;

use App\Models\JobPosting;
use App\Services\JobSources\ArbeitnowSource;
use App\Services\JobSources\AshbySource;
use App\Services\JobSources\GreenhouseSource;
use App\Services\JobSources\HimalayasSource;
use App\Services\JobSources\JobicySource;
use App\Services\JobSources\JobSource;
use App\Services\JobSources\LeverSource;
use App\Services\JobSources\RemoteOkSource;
use App\Services\JobSources\RemotiveSource;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('jobs:sync')]
#[Description('Fetch postings from all configured job sources and upsert them, tagged by tech stack.')]
class SyncJobs extends Command
{
    /** @var array<int, class-string<JobSource>> */
    private array $sourceClasses = [
        RemoteOkSource::class,
        ArbeitnowSource::class,
        JobicySource::class,
        RemotiveSource::class,
        HimalayasSource::class,
        GreenhouseSource::class,
        LeverSource::class,
        AshbySource::class,
    ];

    public function handle(): int
    {
        $stacks = config('job_sources.stacks', []);
        $now = Carbon::now();
        $totalUpserted = 0;

        foreach ($this->sourceClasses as $sourceClass) {
            /** @var JobSource $source */
            $source = new $sourceClass();

            try {
                $postings = $source->fetch();
            } catch (\Throwable $e) {
                $this->error("[{$source->key()}] fetch failed: {$e->getMessage()}");

                continue;
            }

            foreach ($postings as $posting) {
                $tags = $this->tagsFor($stacks, $posting['title'], $posting['description'] ?? '');

                JobPosting::updateOrCreate(
                    [
                        'source' => $source->key(),
                        'external_id' => $posting['external_id'],
                    ],
                    [
                        'title' => $posting['title'],
                        'company' => $posting['company'],
                        'location' => $posting['location'],
                        'is_remote' => $posting['is_remote'],
                        'url' => $posting['url'],
                        'description' => $posting['description'],
                        'tags' => $tags,
                        'posted_at' => $posting['posted_at'] ? Carbon::parse($posting['posted_at']) : null,
                        'fetched_at' => $now,
                    ],
                );

                $totalUpserted++;
            }

            $this->info("[{$source->key()}] synced ".count($postings)." postings");
        }

        $this->info("Done. Upserted {$totalUpserted} postings total.");

        return self::SUCCESS;
    }

    /**
     * @param array<string, array<int, string>> $stacks
     * @return array<int, string>
     */
    private function tagsFor(array $stacks, string $title, ?string $description): array
    {
        $haystack = strtolower($title.' '.strip_tags($description ?? ''));
        $matched = [];

        foreach ($stacks as $stack => $keywords) {
            foreach ($keywords as $keyword) {
                $keyword = strtolower($keyword);
                $isPlainWord = (bool) preg_match('/^[a-z0-9 ]+$/', $keyword);

                $found = $isPlainWord
                    ? preg_match('/\b'.preg_quote($keyword, '/').'\b/', $haystack) === 1
                    : str_contains($haystack, $keyword);

                if ($found) {
                    $matched[] = $stack;

                    break;
                }
            }
        }

        return $matched;
    }
}
