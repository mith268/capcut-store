<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UtmAttribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'session_id',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'fbclid',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
