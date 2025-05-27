<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    use BelongsToTenant, HasUlids;

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
