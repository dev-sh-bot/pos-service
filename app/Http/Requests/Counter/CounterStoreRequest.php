<?php

namespace App\Http\Requests\Counter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CounterStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'name'      => ['required', 'string', 'max:255'],
            'code'      => [
                'required', 'string', 'max:20',
                Rule::unique('counters')->where('branch_id', $this->input('branch_id')),
            ],
            'password'  => ['required', 'string', 'min:4', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
