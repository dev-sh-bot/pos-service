<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $branchId = $this->route('branch')?->id;

        return [
            'name'      => ['required', 'string', 'max:255'],
            'code'      => ['required', 'string', 'max:20', Rule::unique('branches', 'code')->ignore($branchId)],
            'address'   => ['nullable', 'string', 'max:500'],
            'phone'     => ['nullable', 'string', 'max:30'],
            'password'  => ['nullable', 'string', 'min:4', 'confirmed'],
            'srb_pos_id' => ['nullable', 'integer', 'min:1'],
            'srb_pos_user' => ['nullable', 'string', 'max:255'],
            'srb_pos_password' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
