<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\Sequential;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use BelongsToTenant, HasUlids, Sequential;

    protected $fillable = [
        'tenant_id',
        'sequential_id',
        'name',
        'type',
        'bank_name',
        'agency',
        'account_number',
        'current_balance',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
