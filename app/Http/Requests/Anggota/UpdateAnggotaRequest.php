<?php

namespace App\Http\Requests\Anggota;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnggotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'nama' => [
                'required',
                'string',
                'max:150',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' =>
                'Nama anggota harus diisi.',
        ];
    }
}