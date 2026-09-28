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
            'barcode' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $existsInProducts = Product::where('barcode', $value)->exists();
                    $existsInServices = Service::where('barcode', $value)->exists();
                    $existsInDeals = \App\Models\Deal::where('barcode', $value)->where('status', true)->exists();

                    if (! $existsInProducts && ! $existsInServices && ! $existsInDeals) {
                        $fail(__('cart.validation.barcode_not_found'));
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'barcode.required' => __('cart.validation.barcode_required'),
        ];
    }
}
