<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique(
                    User::class,
                    'username'
                )->ignore(
                    $this->user()->id
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' =>
                'Nama harus diisi.',

            'username.required' =>
                'Username harus diisi.',

            'username.alpha_dash' =>
                'Username hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',

            'username.unique' =>
                'Username sudah digunakan.',
        ];
    }
}