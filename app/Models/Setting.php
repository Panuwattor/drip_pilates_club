<?php

namespace App\Models;

use App\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasTranslatedFields;

    protected $translatable = ['label'];

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        // เก็บเป็น array ธรรมดา ไม่เก็บ Eloquent collection
        // เพราะ cache driver บางตัว unserialize model กลับมาไม่ได้
        $all = Cache::rememberForever(
            'settings.all',
            fn () => static::query()->get(['key', 'value', 'type'])
                ->mapWithKeys(fn ($row) => [
                    $row->key => ['value' => $row->value, 'type' => $row->type],
                ])
                ->all()
        );

        if (! array_key_exists($key, $all)) {
            return $default;
        }

        ['value' => $value, 'type' => $type] = $all[$key];

        return match ($type) {
            'int' => (int) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOL),
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    public static function put(string $key, mixed $value): void
    {
        $row = static::find($key);
        $stored = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value;

        if ($row) {
            $row->update(['value' => $stored]);
        } else {
            static::create(['key' => $key, 'value' => $stored]);
        }

        Cache::forget('settings.all');
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('settings.all'));
        static::deleted(fn () => Cache::forget('settings.all'));
    }
}
