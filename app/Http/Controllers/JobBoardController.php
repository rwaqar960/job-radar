<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Illuminate\Http\Request;

class JobBoardController extends Controller
{
    public function index(Request $request)
    {
        $allStacks = array_keys(config('job_sources.stacks', []));
        $selectedStacks = array_intersect($request->query('stacks', []), $allStacks);
        $remoteOnly = $request->boolean('remote');

        $query = JobPosting::query()->orderByDesc('posted_at');

        if (!empty($selectedStacks)) {
            $query->where(function ($q) use ($selectedStacks) {
                foreach ($selectedStacks as $stack) {
                    $q->orWhereJsonContains('tags', $stack);
                }
            });
        }

        if ($remoteOnly) {
            $query->where('is_remote', true);
        }

        $postings = $query->paginate(30)->withQueryString();

        return view('jobs.index', [
            'postings' => $postings,
            'allStacks' => $allStacks,
            'selectedStacks' => $selectedStacks,
            'remoteOnly' => $remoteOnly,
        ]);
    }
}
