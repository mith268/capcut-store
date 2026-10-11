<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class InventoryCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'variant_id',
        'email_encrypted',
        'password_encrypted',
        'notes_encrypted',
        'status',
        'order_id',
        'reserved_at',
        'sold_at',
    ];

    protected $hidden = [
        'email_encrypted',
        'password_encrypted',
        'notes_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'reserved_at' => 'datetime',
            'sold_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }

    public function getEmailAttribute(): ?string
    {
        if (empty($this->email_encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->email_encrypted);
        } catch (Exception $e) {
            return '[Decryption Error]';
        }
    }

    public function setEmailAttribute(?string $value): void
    {
        $this->attributes['email_encrypted'] = !empty($value) ? Crypt::encryptString(trim($value)) : null;
    }

    public function getPasswordAttribute(): ?string
    {
        if (empty($this->password_encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->password_encrypted);
        } catch (Exception $e) {
            return '[Decryption Error]';
        }
    }

    public function setPasswordAttribute(?string $value): void
    {
        $this->attributes['password_encrypted'] = !empty($value) ? Crypt::encryptString(trim($value)) : null;
    }

    public function getNotesAttribute(): ?string
    {
        if (empty($this->notes_encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->notes_encrypted);
        } catch (Exception $e) {
            return null;
        }
    }

    public function setNotesAttribute(?string $value): void
    {
        $this->attributes['notes_encrypted'] = !empty($value) ? Crypt::encryptString(trim($value)) : null;
    }

    public function getMaskedEmailAttribute(): string
    {
        $email = $this->email;
        if (empty($email) || !str_contains($email, '@')) {
            return '******@******';
        }

        [$username, $domain] = explode('@', $email, 2);
        $userLen = strlen($username);

        if ($userLen <= 2) {
            $maskedUser = substr($username, 0, 1) . '*';
        } else {
            $maskedUser = substr($username, 0, 1) . str_repeat('*', min(4, $userLen - 2)) . substr($username, -1);
        }

        return $maskedUser . '@' . $domain;
    }
}
