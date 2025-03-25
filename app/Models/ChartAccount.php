<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\Sequential;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartAccount extends Model
{
    use BelongsToTenant, HasUuids, Sequential;

    protected $fillable = [
        'tenant_id',
        'sequential_id',
        'parent_id',
        'code',
        'name',
        'description',
        'allows_transactions',
        'active',
        'level',
        'order',
        'default_receivable',
        'default_purchase',
        'created_by',
    ];

    protected $casts = [
        'allows_transactions' => 'boolean',
        'active' => 'boolean',
        'level' => 'integer',
        'order' => 'integer',
        'default_receivable' => 'boolean',
        'default_purchase' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ChartAccount::class, 'parent_id')->orderBy('order');
    }

    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateCode($parentId = null)
    {
        if (! $parentId) {
            $lastAccount = static::whereNull('parent_id')
                ->orderBy('code', 'desc')
                ->first();

            $baseCode = $lastAccount ? (int) $lastAccount->code + 1 : 1;

            return (string) $baseCode;
        }

        $parent = static::findOrFail($parentId);
        $lastChild = static::where('parent_id', $parentId)
            ->orderBy('code', 'desc')
            ->first();

        if (! $lastChild) {
            return $parent->code.'.1';
        }

        $parts = explode('.', $lastChild->code);
        $lastPart = (int) end($parts) + 1;
        $parts[count($parts) - 1] = $lastPart;

        return implode('.', $parts);
    }

    public function calculateLevel()
    {
        return substr_count($this->code, '.') + 1;
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeAllowsTransactions($query)
    {
        return $query->where('allows_transactions', true);
    }
}
