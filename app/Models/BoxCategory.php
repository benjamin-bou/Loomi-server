<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoxCategory extends Model
{
    protected $fillable = [
        'short_name',
        'description',
    ];
}
