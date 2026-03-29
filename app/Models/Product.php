<?php

namespace App\Models;

use App\Services\StockService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'id',
        'brand_id',
        'group_id',
        'name',
        'description',
        'sku',
        'cost',
        'price',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class);
    }

    protected static function booted()
    {
        static::created(function ($product) {
            app(StockService::class)->initializeStock($product);
        });
    }
}
