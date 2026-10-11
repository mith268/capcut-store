<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'action',
        'entity',
        'entity_id',
        'ip_address',
        'user_agent',
        'details',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public static function logAction(string $action, ?string $entity = null, ?string $entityId = null, $details = null, ?int $adminId = null): self
    {
        $adminId = $adminId ?? (Auth::check() ? Auth::id() : null);

        $detailsString = is_array($details) ? json_encode($details, JSON_UNESCAPED_SLASHES) : (string) $details;

        return static::create([
            'admin_id' => $adminId,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'ip_address' => Request::ip(),
            'user_agent' => substr((string) Request::userAgent(), 0, 500),
            'details' => $detailsString,
        ]);
    }
}
