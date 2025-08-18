<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'is_public'
    ];

    protected $casts = [
        'is_public' => 'boolean'
    ];

    /**
     * Get setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = "setting.{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return static::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set setting value
     */
    public static function set(string $key, $value, string $type = 'string', string $description = null, bool $isPublic = false): void
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
                'is_public' => $isPublic
            ]
        );

        // Clear cache
        Cache::forget("setting.{$key}");
    }

    /**
     * Cast value to appropriate type
     */
    private static function castValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            case 'array':
                return is_array($value) ? $value : json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Get all public settings (for frontend)
     */
    public static function getPublicSettings(): array
    {
        return Cache::remember('settings.public', 3600, function () {
            return static::where('is_public', true)
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget('settings.public');
        
        // Clear individual setting caches
        $keys = static::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }
    }

    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }

    /**
     * Default settings
     */
    public static function getDefaults(): array
    {
        return [
            'app_name' => [
                'value' => config('app.name', 'POS System'),
                'type' => 'string',
                'description' => 'Nama aplikasi yang akan ditampilkan',
                'is_public' => true
            ],
            'app_logo' => [
                'value' => null,
                'type' => 'file',
                'description' => 'Logo aplikasi (maksimal 2MB)',
                'is_public' => true
            ],
            'company_name' => [
                'value' => 'Your Company Name',
                'type' => 'string',
                'description' => 'Nama perusahaan untuk struk',
                'is_public' => true
            ],
            'company_address' => [
                'value' => 'Your Company Address',
                'type' => 'string',
                'description' => 'Alamat perusahaan untuk struk',
                'is_public' => true
            ],
            'company_phone' => [
                'value' => 'Your Phone Number',
                'type' => 'string',
                'description' => 'Nomor telepon perusahaan untuk struk',
                'is_public' => true
            ],
            'ipaymu_va' => [
                'value' => config('services.ipaymu.va'),
                'type' => 'string',
                'description' => 'iPaymu Virtual Account',
                'is_public' => false
            ],
            'ipaymu_api_key' => [
                'value' => config('services.ipaymu.api_key'),
                'type' => 'string',
                'description' => 'iPaymu API Key',
                'is_public' => false
            ],
            'ipaymu_environment' => [
                'value' => config('services.ipaymu.environment', 'sandbox'),
                'type' => 'string',
                'description' => 'iPaymu Environment (sandbox/production)',
                'is_public' => false
            ],
            'receipt_footer' => [
                'value' => 'Terima kasih atas kunjungan Anda!',
                'type' => 'string',
                'description' => 'Footer yang ditampilkan di struk',
                'is_public' => true
            ]
        ];
    }

    /**
     * Initialize default settings
     */
    public static function initializeDefaults(): void
    {
        $defaults = static::getDefaults();
        
        foreach ($defaults as $key => $config) {
            if (!static::where('key', $key)->exists()) {
                static::set(
                    $key,
                    $config['value'],
                    $config['type'],
                    $config['description'],
                    $config['is_public']
                );
            }
        }
    }
}