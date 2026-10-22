<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class AddToPurchaseCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barcode'    => 'nullable|string',
            'product_id' => 'nullable|exists:products,id',
        ];
    }
}
