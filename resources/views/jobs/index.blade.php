<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Job Radar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: -apple-system, Segoe UI, Roboto, sans-serif; max-width: 900px; margin: 0 auto; padding: 24px; color: #1a1a1a; }
        h1 { font-size: 22px; margin-bottom: 4px; }
        .subtitle { color: #666; margin-bottom: 20px; font-size: 14px; }
        form.filters { display: flex; flex-wrap: wrap; gap: 10px 16px; align-items: center; padding: 14px; background: #f5f5f5; border-radius: 8px; margin-bottom: 20px; }
        form.filters label { font-size: 14px; display: flex; align-items: center; gap: 5px; cursor: pointer; }
        form.filters button { padding: 6px 14px; border: 0; background: #1a1a1a; color: #fff; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .job { padding: 14px 0; border-bottom: 1px solid #e5e5e5; }
        .job h2 { font-size: 16px; margin: 0 0 4px; }
        .job h2 a { color: #1a1a1a; text-decoration: none; }
        .job h2 a:hover { text-decoration: underline; }
        .job .meta { font-size: 13px; color: #666; margin-bottom: 6px; }
        .job .tags span { display: inline-block; font-size: 11px; background: #eef; color: #334; padding: 2px 7px; border-radius: 10px; margin-right: 4px; }
        .empty { padding: 40px 0; text-align: center; color: #888; }
        .pagination { margin-top: 20px; }
        .pagination a, .pagination span { margin-right: 8px; font-size: 13px; }
    </style>
</head>
<body>
    <h1>Job Radar</h1>
    <p class="subtitle">Tech-stack-filtered job postings, pulled from public job board APIs and company ATS feeds.</p>

    <form class="filters" method="get">
        @foreach ($allStacks as $stack)
            <label>
                <input type="checkbox" name="stacks[]" value="{{ $stack }}" {{ in_array($stack, $selectedStacks) ? 'checked' : '' }}>
                {{ ucfirst($stack) }}
            </label>
        @endforeach
        <label>
            <input type="checkbox" name="remote" value="1" {{ $remoteOnly ? 'checked' : '' }}>
            Remote only
        </label>
        <button type="submit">Filter</button>
    </form>

    @forelse ($postings as $posting)
        <div class="job">
            <h2><a href="{{ $posting->url }}" target="_blank" rel="noopener">{{ $posting->title }}</a></h2>
            <div class="meta">
                {{ $posting->company }}
                @if ($posting->location) &middot; {{ $posting->location }} @endif
                @if ($posting->is_remote) &middot; Remote @endif
                &middot; via {{ ucfirst($posting->source) }}
                @if ($posting->posted_at) &middot; {{ $posting->posted_at->diffForHumans() }} @endif
            </div>
            <div class="tags">
                @foreach ($posting->tags ?? [] as $tag)
                    <span>{{ $tag }}</span>
                @endforeach
            </div>
        </div>
    @empty
        <div class="empty">No postings match these filters yet. Try different stacks, or run <code>php artisan jobs:sync</code> to fetch fresh postings.</div>
    @endforelse

    <div class="pagination">
        {{ $postings->links() }}
    </div>
</body>
</html>
