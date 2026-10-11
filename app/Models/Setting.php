<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'is_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
        ];
    }

    public static function get(string $key, $default = null)
    {
        $setting = Cache::remember("setting_{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        if ($setting->is_encrypted && !empty($setting->value)) {
            try {
                return Crypt::decryptString($setting->value);
            } catch (Exception $e) {
                return $default;
            }
        }

        return $setting->value ?? $default;
    }

    public static function set(string $key, $value, bool $isEncrypted = false, string $group = 'general'): self
    {
        $storedValue = $value;
        if ($isEncrypted && !empty($value)) {
            $storedValue = Crypt::encryptString((string) $value);
        }

        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storedValue,
                'is_encrypted' => $isEncrypted,
                'group' => $group,
            ]
        );

        Cache::forget("setting_{$key}");
        Cache::forget('all_public_settings');

        return $setting;
    }

    public static function getAllPublic(): array
    {
        return Cache::remember('all_public_settings', 3600, function () {
            $settings = static::where('is_encrypted', false)->get();
            $result = [];
            foreach ($settings as $setting) {
                $result[$setting->key] = $setting->value;
            }
            return $result;
        });
    }
}
