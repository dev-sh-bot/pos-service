<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password'   => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active'  => ['nullable', 'boolean'],
            'roles'      => ['nullable', 'array'],
            'roles.*'    => ['integer', 'exists:roles,id'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
            'branches'   => ['nullable', 'array'],
            'branches.*' => ['integer', 'exists:branches,id'],
            'counters'   => ['nullable', 'array'],
            'counters.*' => ['integer', 'exists:counters,id'],
        ];
    }
}
