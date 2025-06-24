<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'title',
        'excerpt',
        'content',
        'image_url',
        'published',
    ];

    protected $casts = [
        'published' => 'boolean',
    ];
}
