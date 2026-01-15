<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_address' => ['required', 'string', 'max:500'],
            'shipping_province_id' => ['required', 'integer', 'exists:provinces,id'],
            'shipping_city_id' => ['required', 'integer', 'exists:cities,id'],
            'shipping_district_id' => ['required', 'integer', 'exists:districts,id'],
            'shipping_postal_code' => ['nullable', 'regex:/^[0-9]{5}$/', 'max:5'],
            'notes' => ['nullable', 'string', 'max:500'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'shipping_courier' => ['nullable', 'string', 'max:50'],
            'shipping_service' => ['nullable', 'string', 'max:50'],
            'shipping_etd' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Nama penerima harus diisi',
            'customer_email.required' => 'Email harus diisi',
            'customer_email.email' => 'Email tidak valid',
            'customer_phone.required' => 'Nomor telepon harus diisi',
            'customer_phone.regex' => 'Format nomor telepon tidak valid (gunakan +62, 62, atau 0)',
            'shipping_address.required' => 'Alamat pengiriman harus diisi',
            'shipping_province_id.required' => 'Provinsi harus dipilih',
            'shipping_province_id.exists' => 'Provinsi yang dipilih tidak valid',
            'shipping_city_id.required' => 'Kabupaten/Kota harus dipilih',
            'shipping_city_id.exists' => 'Kabupaten/Kota yang dipilih tidak valid',
            'shipping_district_id.required' => 'Kecamatan harus dipilih',
            'shipping_district_id.exists' => 'Kecamatan yang dipilih tidak valid',
            'shipping_postal_code.required' => 'Kode pos harus diisi',
            'shipping_postal_code.regex' => 'Kode pos harus 5 digit',
            'payment_method.required' => 'Metode pembayaran harus dipilih',
            'payment_method.in' => 'Metode pembayaran tidak valid',
        ];
    }
}
