<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\Sequential;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayablePayment extends Model
{
    use BelongsToTenant, HasUlids, Sequential;

    protected $table = 'payables_payments';

    protected $fillable = [
        'tenant_id',
        'sequential_id',
        'payable_id',
        'payment_method_id',
        'account_id',
        'transaction_id',
        'payment_date',
        'total_amount',
        'paid_amount',
        'fees',
        'discount',
        'remaining_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'total_amount' => 'float',
        'paid_amount' => 'float',
        'fees' => 'float',
        'discount' => 'float',
        'remaining_amount' => 'float',
    ];

    public function payable(): BelongsTo
    {
        return $this->belongsTo(Payable::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
