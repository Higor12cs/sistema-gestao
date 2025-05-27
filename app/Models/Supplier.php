<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\Sequential;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supplier extends Model
{
    use BelongsToTenant, HasUlids, Sequential;

    protected $fillable = [
        'tenant_id',
        'sequential_id',
        'first_name',
        'last_name',
        'legal_name',
        'cpf_cnpj',
        'rg',
        'ie',
        'birth_date',
        'email',
        'phone',
        'whatsapp',
        'zip_code',
        'address',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'country',
        'active',
        'created_by',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'active' => 'boolean',
    ];

    protected $appends = ['name'];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
