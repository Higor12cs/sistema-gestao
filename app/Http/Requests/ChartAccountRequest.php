<?php

namespace App\Http\Requests;

use App\Models\ChartAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ChartAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'allows_transactions' => $this->boolean('allows_transactions'),
            'active' => $this->boolean('active'),
            'default_receivable' => $this->boolean('default_receivable'),
            'default_purchase' => $this->boolean('default_purchase'),
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|uuid|exists:chart_accounts,id',
            'allows_transactions' => 'required|boolean',
            'active' => 'required|boolean',
            'default_receivable' => 'nullable|boolean',
            'default_purchase' => 'nullable|boolean',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $account = ChartAccount::find($this->route('chart_account'));
            if ($account && $account->children()->count() > 0) {
                $rules['allows_transactions'] = 'required|boolean|in:0';
            }
        }

        return $rules;
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        $data['tenant_id'] = Auth::user()->tenant_id;
        $data['created_by'] = Auth::id();

        if ($this->isMethod('POST')) {
            $data['code'] = ChartAccount::generateCode($data['parent_id'] ?? null);

            $data['level'] = $data['parent_id']
                ? ChartAccount::find($data['parent_id'])->level + 1
                : 1;

            $maxOrder = ChartAccount::where('parent_id', $data['parent_id'])
                ->where('tenant_id', $data['tenant_id'])
                ->max('order') ?? 0;

            $data['order'] = $maxOrder + 1;
        }

        return $data;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'name.string' => 'O nome deve ser um texto',
            'name.max' => 'O nome não pode ter mais que 255 caracteres',
            'parent_id.exists' => 'O plano de conta pai selecionado não existe',
            'allows_transactions.in' => 'Esta conta possui subcontas e não pode receber lançamentos',
            'active.required' => 'O campo ativo é obrigatório',
            'active.boolean' => 'O campo ativo deve ser um booleano',
            'default_receivable.boolean' => 'O campo padrão a receber deve ser um booleano',
            'default_purchase.boolean' => 'O campo padrão a pagar deve ser um booleano',
        ];
    }
}
