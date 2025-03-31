<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'active' => $this->boolean('active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'cpf_cnpj' => 'nullable|string|max:20',
            'rg' => 'nullable|string|max:20',
            'ie' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'zip_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:10',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'country' => 'nullable|string|max:100',
            'active' => 'required|boolean',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        $data['tenant_id'] = auth()->user()->tenant_id;
        $data['created_by'] = auth()->id();

        return $data;
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'O nome é obrigatório',
            'first_name.string' => 'O nome deve ser um texto',
            'first_name.max' => 'O nome não pode ter mais que 255 caracteres',
            'last_name.string' => 'O sobrenome deve ser um texto',
            'last_name.max' => 'O sobrenome não pode ter mais que 255 caracteres',
            'legal_name.string' => 'O nome legal deve ser um texto',
            'legal_name.max' => 'O nome legal não pode ter mais que 255 caracteres',
            'cpf_cnpj.string' => 'O CPF/CNPJ deve ser um texto',
            'cpf_cnpj.max' => 'O CPF/CNPJ não pode ter mais que 20 caracteres',
            'rg.string' => 'O RG deve ser um texto',
            'rg.max' => 'O RG não pode ter mais que 20 caracteres',
            'ie.string' => 'A inscrição estadual deve ser um texto',
            'ie.max' => 'A inscrição estadual não pode ter mais que 20 caracteres',
            'birth_date.date' => 'A data de nascimento deve ser uma data válida',
            'email.email' => 'O e-mail deve ser um endereço válido',
            'email.max' => 'O e-mail não pode ter mais que 255 caracteres',
            'phone.string' => 'O telefone deve ser um texto',
            'phone.max' => 'O telefone não pode ter mais que 20 caracteres',
            'whatsapp.string' => 'O WhatsApp deve ser um texto',
            'whatsapp.max' => 'O WhatsApp não pode ter mais que 20 caracteres',
            'zip_code.string' => 'O CEP deve ser um texto',
            'zip_code.max' => 'O CEP não pode ter mais que 10 caracteres',
            'address.string' => 'O endereço deve ser um texto',
            'address.max' => 'O endereço não pode ter mais que 255 caracteres',
            'number.string' => 'O número deve ser um texto',
            'number.max' => 'O número não pode ter mais que 10 caracteres',
            'complement.string' => 'O complemento deve ser um texto',
            'complement.max' => 'O complemento não pode ter mais que 255 caracteres',
            'neighborhood.string' => 'O bairro deve ser um texto',
            'neighborhood.max' => 'O bairro não pode ter mais que 100 caracteres',
            'city.string' => 'A cidade deve ser um texto',
            'city.max' => 'A cidade não pode ter mais que 100 caracteres',
            'state.string' => 'O estado deve ser um texto',
            'state.max' => 'O estado não pode ter mais que 2 caracteres',
            'country.string' => 'O país deve ser um texto',
            'country.max' => 'O país não pode ter mais que 100 caracteres',
        ];
    }
}
