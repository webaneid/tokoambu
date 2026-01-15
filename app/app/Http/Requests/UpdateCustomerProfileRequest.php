<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customerId = auth('customer')->id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:customers,email,' . $customerId],
            'phone' => ['required', 'string', 'max:20'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'postal_code' => ['nullable', 'string', 'max:5'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap diperlukan',
            'name.string' => 'Nama harus berupa teks',
            'name.max' => 'Nama maksimal 255 karakter',
            'email.required' => 'Email diperlukan',
            'email.email' => 'Email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'phone.required' => 'Nomor telepon diperlukan',
            'phone.string' => 'Nomor telepon harus berupa teks',
            'phone.max' => 'Nomor telepon maksimal 20 karakter',
            'whatsapp_number.string' => 'Nomor WhatsApp harus berupa teks',
            'whatsapp_number.max' => 'Nomor WhatsApp maksimal 20 karakter',
            'address.string' => 'Alamat harus berupa teks',
            'address.max' => 'Alamat maksimal 500 karakter',
            'province_id.exists' => 'Provinsi tidak valid',
            'city_id.exists' => 'Kabupaten/Kota tidak valid',
            'district_id.exists' => 'Kecamatan tidak valid',
            'postal_code.string' => 'Kode pos harus berupa teks',
            'postal_code.max' => 'Kode pos maksimal 5 karakter',
            'bank_name.string' => 'Nama bank harus berupa teks',
            'bank_name.max' => 'Nama bank maksimal 100 karakter',
            'account_number.string' => 'Nomor rekening harus berupa teks',
            'account_number.max' => 'Nomor rekening maksimal 50 karakter',
            'account_name.string' => 'Nama pemilik rekening harus berupa teks',
            'account_name.max' => 'Nama pemilik rekening maksimal 255 karakter',
        ];
    }
}
