<?php

namespace App\Http\Requests;

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
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'ID Produk harus ada',
            'product_id.exists' => 'Produk tidak ditemukan',
            'variant_id.exists' => 'Variasi tidak ditemukan',
            'quantity.required' => 'Kuantitas harus ada',
            'quantity.min' => 'Kuantitas minimal 1',
            'quantity.max' => 'Kuantitas maksimal 99',
        ];
    }
}
