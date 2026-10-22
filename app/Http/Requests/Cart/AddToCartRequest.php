<?php

namespace App\Http\Requests\Cart;

use App\Models\Product;
use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barcode' => ['nullable', 'string'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (! $this->filled('barcode') && ! $this->filled('product_id')) {
                $validator->errors()->add('barcode', __('cart.validation.barcode_required'));
            }
        });
    }
}
