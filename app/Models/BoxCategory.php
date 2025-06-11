<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BoxCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'short_name',
        'description',
    ];
}
