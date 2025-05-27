<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\Sequential;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use BelongsToTenant, HasFactory, HasUlids, Sequential;

    protected $fillable = [
        'tenant_id',
        'sequential_id',
        'supplier_id',
        'issue_date',
        'discount',
        'fees',
        'total_cost',
        'observation',
        'created_by',
    ];

    protected $casts = [
        'issue_date' => 'datetime',
        'total_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'fees' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payables(): HasMany
    {
        return $this->hasMany(Payable::class);
    }

    public function hasPayables(): bool
    {
        return $this->payables()->exists();
    }

    public function isEditable(): bool
    {
        return ! $this->hasPayables();
    }
}
