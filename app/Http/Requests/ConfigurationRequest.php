<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => 'required|string|max:255',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        $data['tenant_id'] = Auth::user()->tenant_id;
        $data['created_by'] = Auth::id();

        return $data;
    }

    public function messages(): array
    {
        return [
            'value.required' => 'O nome é obrigatório',
            'value.string' => 'O nome deve ser um texto',
            'value.max' => 'O nome não pode ter mais que 255 caracteres',
        ];
    }
}
