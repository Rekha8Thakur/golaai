<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $primaryKey = 'video_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'video_id',
        'title',
        'thumbnail_url',
        'transcript',
        'summary',
        'notes',
        'qa',
        'mcqs',
        'action_items',
    ];

    protected function casts(): array
    {
        return [
            'transcript' => 'array',
            'qa' => 'array',
            'mcqs' => 'array',
            'action_items' => 'array',
        ];
    }
}
