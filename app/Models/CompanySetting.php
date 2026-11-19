<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class CompanySetting extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'company_name',
        'tagline',
        'logo_path',
        'address',
        'phone',
        'email',
        'website',
        'license_number',
        'city',
        'invoice_footer_note',
    ];

    private static ?self $cachedInstance = null;

    public static function current(): self
    {
        if (self::$cachedInstance !== null) {
            return self::$cachedInstance;
        }

        if (! Schema::hasTable('company_settings')) {
            return self::$cachedInstance = new self([
                'company_name' => config('app.name', 'Aplikasi Benih'),
            ]);
        }

        $setting = self::query()->first();

        if (! $setting) {
            $setting = new self([
                'company_name' => config('app.name', 'Aplikasi Benih'),
            ]);
        }

        return self::$cachedInstance = $setting;
    }

    public static function clearCachedInstance(): void
    {
        self::$cachedInstance = null;
    }

    public function logoUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return asset($this->logo_path);
    }
}
