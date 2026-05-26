<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImageFile extends Model
{
    use HasFactory;

    protected $table = 'images';

    protected $fillable = [
        'user_id',
        'original_path',
        'variants',
        'mime',
        'size',
    ];

    protected $casts = [
        'variants' => 'array',
    ];
}
