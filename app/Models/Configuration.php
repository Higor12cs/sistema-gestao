<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'description',
        'name',
        'value',
        'type',
    ];

    public static function getConfigurationValue(string $name, $default = null)
    {
        $config = self::where('name', $name)->first();

        return $config ? $config->value : $default;
    }
}
