<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\Sequential;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use BelongsToTenant, HasUlids, Sequential;

    protected $fillable = [
        'tenant_id',
        'sequential_id',
        'stock_id',
        'product_id',
        'type',
        'source_type',
        'source_id',
        'previous_quantity',
        'quantity',
        'new_quantity',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'previous_quantity' => 'decimal:2',
        'quantity' => 'decimal:2',
        'new_quantity' => 'decimal:2',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
