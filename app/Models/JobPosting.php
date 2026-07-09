<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    protected $fillable = [
        'source',
        'external_id',
        'title',
        'company',
        'location',
        'is_remote',
        'url',
        'description',
        'tags',
        'posted_at',
        'fetched_at',
    ];

    protected $casts = [
        'is_remote' => 'boolean',
        'tags' => 'array',
        'posted_at' => 'datetime',
        'fetched_at' => 'datetime',
    ];
}
