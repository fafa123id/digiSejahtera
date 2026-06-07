<?php

namespace App\Http\Requests\Angsuran;

use Illuminate\Foundation\Http\FormRequest;

class StoreAngsuranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'periode' => [
                'required',
                'date',
            ],

            'tanggal_pembayaran' => [
                'required',
                'date',
            ],

            'nominal_angsuran' => [
                'required',
                'numeric',
                'min:0',
            ],

            'keterangan' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'periode.required' =>
                'Periode angsuran harus diisi.',

            'tanggal_pembayaran.required' =>
                'Tanggal pembayaran harus diisi.',

            'nominal_angsuran.required' =>
                'Nominal angsuran harus diisi.',

            'nominal_angsuran.min' =>
                'Nominal angsuran tidak boleh negatif.',
        ];
    }
}