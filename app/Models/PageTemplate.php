<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'theme_preset',
        'sections',
        'thumbnail',
    ];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
        ];
    }
}
