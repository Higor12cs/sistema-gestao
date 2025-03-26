<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkTransactionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transaction_ids' => 'required|array|min:1',
            'transaction_ids.*' => 'exists:transactions,id',
            'transaction_date' => 'nullable|date',
            'reconciled' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'transaction_ids.required' => 'Você deve selecionar pelo menos uma transação.',
            'transaction_ids.array' => 'O formato da seleção de transações é inválido.',
            'transaction_ids.min' => 'Você deve selecionar pelo menos uma transação.',
            'transaction_ids.*.exists' => 'Uma das transações selecionadas não existe.',
            'transaction_date.date' => 'A data da transação deve ser uma data válida.',
            'reconciled.boolean' => 'O status de conciliação deve ser verdadeiro ou falso.',
        ];
    }
}
