<?php

namespace App\Services\JobSources;

interface JobSource
{
    /**
     * Unique key stored in the `source` column, used together with
     * external_id for dedupe (e.g. "remoteok", "greenhouse").
     */
    public function key(): string;

    /**
     * Fetch and normalize postings from this source.
     *
     * @return array<int, array{
     *     external_id: string,
     *     title: string,
     *     company: string,
     *     location: ?string,
     *     is_remote: bool,
     *     url: string,
     *     description: ?string,
     *     posted_at: ?string,
     * }>
     */
    public function fetch(): array;
}
