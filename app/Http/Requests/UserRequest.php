<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')
                    ->where('tenant_id', Auth::user()->tenant_id)
                    ->ignore($this->user),
            ],
            'role_id' => ['nullable', 'exists:roles,id'],
        ];

        if (! $this->user) {
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        return $rules;
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        if (isset($validated['password']) && $validated['password']) {
            $validated['password'] = bcrypt($validated['password']);
        }

        if (! $this->user) {
            $validated['tenant_id'] = Auth::user()->tenant_id;
        }

        if (isset($validated['role_id'])) {
            unset($validated['role_id']);
        }

        return $validated;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O campo nome é obrigatório.',
            'name.string' => 'O campo nome deve ser uma string.',
            'name.max' => 'O campo nome deve ter no máximo 255 caracteres.',
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.string' => 'O campo e-mail deve ser uma string.',
            'email.email' => 'O campo e-mail deve ser um e-mail válido.',
            'email.max' => 'O campo e-mail deve ter no máximo 255 caracteres.',
            'email.unique' => 'O e-mail informado já está em uso.',
            'password.required' => 'O campo senha é obrigatório.',
            'password.confirmed' => 'O campo senha não confere com a confirmação.',
            'password.min' => 'O campo senha deve ter no mínimo 8 caracteres.',
            'password.max' => 'O campo senha deve ter no máximo 255 caracteres.',
            'role_id.exists' => 'O papel selecionado não existe.',
        ];
    }
}
