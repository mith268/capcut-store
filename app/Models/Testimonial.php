<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_initial',
        'review_text',
        'rating',
        'verified',
        'published',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'verified' => 'boolean',
            'published' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('published', true)->orderBy('display_order');
    }

    public function getInitialAttribute(): string
    {
        if (!empty($this->customer_initial)) {
            return strtoupper($this->customer_initial);
        }
        return strtoupper(substr($this->customer_name, 0, 1));
    }
}
