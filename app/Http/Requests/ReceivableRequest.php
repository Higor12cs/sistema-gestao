<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceivableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $receivables = $this->receivables;

        foreach ($receivables as $key => $receivable) {
            $receivables[$key]['total_amount'] = $this->convertToNumber($receivable['total_amount']);
        }

        $this->merge([
            'receivables' => $receivables,
        ]);
    }

    public function rules(): array
    {
        if ($this->routeIs('receivables.update')) {
            return [
                'due_date' => 'required|date',
            ];
        }

        return [
            'receivables' => 'required|array|min:1',
            'receivables.*.customer_id' => 'required|exists:customers,id',
            'receivables.*.chart_account_id' => 'required|exists:chart_accounts,id',
            'receivables.*.payment_method_id' => 'required|exists:payment_methods,id',
            'receivables.*.issue_date' => 'required|date',
            'receivables.*.due_date' => 'required|date|after_or_equal:receivables.*.issue_date',
            'receivables.*.total_amount' => 'required|numeric|min:0.01',
            'receivables.*.description' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'receivables.required' => 'É necessário informar pelo menos um recebível.',
            'receivables.min' => 'É necessário informar pelo menos um recebível.',
            'receivables.*.customer_id.required' => 'O cliente é obrigatório.',
            'receivables.*.payment_method_id.required' => 'O método de pagamento é obrigatório.',
            'receivables.*.issue_date.required' => 'A data de emissão é obrigatória.',
            'receivables.*.due_date.required' => 'A data de vencimento é obrigatória.',
            'receivables.*.due_date.after_or_equal' => 'A data de vencimento deve ser posterior ou igual à data de emissão.',
            'receivables.*.total_amount.required' => 'O valor total é obrigatório.',
            'receivables.*.total_amount.numeric' => 'O valor total deve ser um número.',
            'receivables.*.total_amount.min' => 'O valor total deve ser maior que zero.',
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
