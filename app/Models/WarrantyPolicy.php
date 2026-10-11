<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarrantyPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'warranty_days',
        'policy_content',
        'eligible_issues',
        'excluded_issues',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'warranty_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
