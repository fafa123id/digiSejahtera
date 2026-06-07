<?php

namespace App\Http\Requests\Simpanan;

use Illuminate\Foundation\Http\FormRequest;

class StoreSimpananRequest extends FormRequest
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

            'simpanan_pokok' => [
                'nullable',
                'numeric',
            ],

            'simpanan_wajib' => [
                'nullable',
                'numeric',
            ],

            'simpanan_sukarela' => [
                'nullable',
                'numeric',
            ],

            'simpanan_hari_raya' => [
                'nullable',
                'numeric',
            ],

            'simpanan_rekreasi' => [
                'nullable',
                'numeric',
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
                'Periode simpanan harus diisi.',

            'periode.date' =>
                'Periode simpanan tidak valid.',
        ];
    }
}