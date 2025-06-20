<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BoxImage extends Model
{
    use HasFactory;

    protected $table = 'boxes_images';

    protected $fillable = [
        'boxes_images_id',
        'publication_date',
        'link',
        'alt',
        'box_id',
    ];

    protected $casts = [
        'publication_date' => 'datetime',
    ];

    /**
     * Relation avec la table boxes
     */
    public function box()
    {
        return $this->belongsTo(Box::class);
    }
}
