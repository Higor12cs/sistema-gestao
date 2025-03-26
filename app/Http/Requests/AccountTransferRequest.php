<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'amount' => $this->convertToNumber($this->amount),
        ]);
    }

    public function rules(): array
    {
        return [
            'source_account_id' => 'required|exists:accounts,id',
            'destination_account_id' => 'required|exists:accounts,id|different:source_account_id',
            'amount' => 'required|numeric|gt:0',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'source_account_id.required' => 'A conta de origem é obrigatória.',
            'source_account_id.exists' => 'A conta de origem selecionada não existe.',
            'destination_account_id.required' => 'A conta de destino é obrigatória.',
            'destination_account_id.exists' => 'A conta de destino selecionada não existe.',
            'destination_account_id.different' => 'A conta de destino deve ser diferente da conta de origem.',
            'amount.required' => 'O valor da transferência é obrigatório.',
            'amount.numeric' => 'O valor da transferência deve ser um número.',
            'amount.gt' => 'O valor da transferência deve ser maior que zero.',
            'transfer_date.required' => 'A data da transferência é obrigatória.',
            'transfer_date.date' => 'A data da transferência deve ser uma data válida.',
            'notes.max' => 'As observações não podem ter mais que 255 caracteres.',
        ];
    }

    private function convertToNumber($value): ?float
    {
        if (is_null($value)) {
            return null;
        }

        $value = str_replace(',', '.', $value);

        return floatval(preg_replace('/[^\d.]/', '', $value));
    }
}
