<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\Sequential;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payable extends Model
{
    use BelongsToTenant, HasUlids, Sequential;

    protected $fillable = [
        'tenant_id',
        'sequential_id',
        'purchase_id',
        'chart_account_id',
        'payment_method_id',
        'is_manual',
        'supplier_id',
        'issue_date',
        'due_date',
        'total_amount',
        'paid_amount',
        'fees',
        'discount',
        'remaining_amount',
        'status',
        'description',
        'created_by',
    ];

    protected $casts = [
        'is_manual' => 'boolean',
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function chartAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
