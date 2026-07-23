<?php

namespace App\Http\Requests\Anggota;

use App\Models\Anggota;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnggotaRequest extends FormRequest
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
            'agama' => [
                'required',
                Rule::in([
                    Anggota::AGAMA_ISLAM,
                    Anggota::AGAMA_NONISLAM,
                ]),
            ],
            'tanggal_masuk' => [
                'required',
                'date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' =>
            'Nama anggota harus diisi.',

            'tanggal_masuk.required' =>
            'Tanggal bergabung harus diisi.',

            'tanggal_masuk.date' =>
            'Tanggal bergabung tidak valid.',
            'agama.required' =>
                'Agama harus diisi.',
            'agama.in' =>
                'Agama harus diisi',
        ];
    }
}
