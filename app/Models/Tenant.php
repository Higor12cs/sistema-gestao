<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'trial_ends_at',
        'subscription_ends_at',
        'subscription_cancelled_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function configurations(): HasMany
    {
        return $this->hasMany(Configuration::class);
    }
}
