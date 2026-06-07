<?php

namespace App\Http\Requests\Pinjaman;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePinjamanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'tanggal_pinjaman' => [
                'required',
                'date',
            ],

            'jenis_pinjaman' => [
                'required',
                Rule::in([
                    'reguler',
                    'sebrak',
                ]),
            ],

            'nominal_pinjaman' => [
                'required',
                'numeric',
                'min:1',
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
            'tanggal_pinjaman.required' =>
                'Tanggal pinjaman harus diisi.',

            'jenis_pinjaman.required' =>
                'Jenis pinjaman harus dipilih.',

            'nominal_pinjaman.required' =>
                'Nominal pinjaman harus diisi.',

            'nominal_pinjaman.min' =>
                'Nominal pinjaman harus lebih besar dari nol.',
        ];
    }
}