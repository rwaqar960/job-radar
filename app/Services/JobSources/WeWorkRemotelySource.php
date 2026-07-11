<?php

namespace App\Services\JobSources;

use Illuminate\Support\Facades\Http;

class WeWorkRemotelySource implements JobSource
{
    public function key(): string
    {
        return 'weworkremotely';
    }

    public function fetch(): array
    {
        $body = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])
            ->get('https://weworkremotely.com/remote-jobs.rss')
            ->throw()
            ->body();

        $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);
        $jobs = [];

        foreach ($xml->channel->item as $item) {
            // WWR titles are formatted "Company: Job Title".
            $title = (string) $item->title;
            [$company, $position] = str_contains($title, ': ')
                ? explode(': ', $title, 2)
                : ['Unknown', $title];

            $location = trim(implode(', ', array_filter([(string) $item->state, (string) $item->region])));

            $jobs[] = [
                'external_id' => (string) $item->guid,
                'title' => $position,
                'company' => $company,
                'location' => $location ?: null,
                'is_remote' => true,
                'url' => (string) $item->link,
                'description' => (string) $item->description,
                'posted_at' => (string) $item->pubDate ?: null,
            ];
        }

        return $jobs;
    }
}
