<?php

namespace App\Http\Requests\Counter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CounterUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $counterId = $this->route('counter')?->id;
        $branchId  = $this->input('branch_id', $this->route('counter')?->branch_id);

        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'name'      => ['required', 'string', 'max:255'],
            'code'      => [
                'required', 'string', 'max:20',
                Rule::unique('counters')->where('branch_id', $branchId)->ignore($counterId),
            ],
            'password'  => ['nullable', 'string', 'min:4', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
