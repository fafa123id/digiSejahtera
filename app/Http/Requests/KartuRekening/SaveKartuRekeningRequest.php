<?php

namespace App\Http\Requests\KartuRekening;

use App\Models\Anggota;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Throwable;

class SaveKartuRekeningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $changes = collect(
            $this->input(
                'changes',
                []
            )
        )
            ->map(
                function (
                    array $change
                ): array {
                    if (
                        !array_key_exists(
                            'value',
                            $change
                        )
                    ) {
                        return $change;
                    }

                    $change['value'] =
                        $this->normalisasiNominal(
                            $change['value']
                        );

                    return $change;
                }
            )
            ->values()
            ->all();

        $this->merge([
            'changes' =>
            $changes,
        ]);
    }

    public function rules(): array
    {
        return [
            'tahun' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
            ],

            'changes' => [
                'required',
                'array',
                'min:1',
            ],

            'changes.*.anggota_id' => [
                'required',
                'integer',
                'exists:anggotas,id',
            ],

            'changes.*.periode' => [
                'required',
                'date_format:Y-m',
            ],

            'changes.*.section' => [
                'required',
                Rule::in([
                    'anggota',
                    'simpanan',
                    'reguler',
                    'sebrak',
                ]),
            ],

            'changes.*.field' => [
                'required',
                'string',
            ],

            'changes.*.value' => [
                'nullable',
            ],

            'changes.*.action' => [
                'nullable',
                Rule::in([
                    'create_pinjaman',
                    'update_pinjaman',
                    'create_angsuran',
                    'update_angsuran',
                ]),
            ],

            'changes.*.entry_id' => [
                'nullable',
                'integer',
            ],

            'changes.*.client_key' => [
                'nullable',
                'string',
                'max:120',
            ],
        ];
    }

    public function withValidator(
        Validator $validator
    ): void {
        $validator->after(
            function (
                Validator $validator
            ): void {
                $tahun =
                    (int) $this->input(
                        'tahun'
                    );

                foreach (
                    $this->input(
                        'changes',
                        []
                    )
                    as $index => $change
                ) {
                    $this->validasiPeriode(
                        validator: $validator,

                        index: $index,

                        tahun: $tahun,

                        periode: $change['periode']
                            ?? null,
                    );

                    $section =
                        $change['section']
                        ?? null;

                    if (
                        $section
                        === 'anggota'
                    ) {
                        $this->validasiAnggota(
                            validator: $validator,

                            index: $index,

                            field: $change['field']
                                ?? null,

                            value: $change['value']
                                ?? null,
                        );

                        continue;
                    }

                    if (
                        $section
                        === 'simpanan'
                    ) {
                        $this->validasiSimpanan(
                            validator: $validator,

                            index: $index,

                            field: $change['field']
                                ?? null,

                            value: $change['value']
                                ?? null,
                        );

                        continue;
                    }

                    if (
                        in_array(
                            $section,
                            [
                                'reguler',
                                'sebrak',
                            ],
                            true
                        )
                    ) {
                        $this->validasiTransaksiPinjaman(
                            validator: $validator,

                            index: $index,

                            action: $change['action']
                                ?? null,

                            entryId: $change['entry_id']
                                ?? null,

                            field: $change['field']
                                ?? null,

                            value: $change['value']
                                ?? null,
                        );
                    }
                }
            }
        );
    }

    private function validasiPeriode(
        Validator $validator,
        int $index,
        int $tahun,
        ?string $periode
    ): void {
        if (!$periode) {
            return;
        }

        try {
            $tahunPeriode =
                (int) CarbonImmutable
                    ::createFromFormat(
                        'Y-m',
                        $periode
                    )
                    ->format(
                        'Y'
                    );
        } catch (Throwable) {
            return;
        }

        if (
            $tahunPeriode
            !== $tahun
        ) {
            $validator
                ->errors()
                ->add(
                    "changes.{$index}.periode",
                    'Periode tidak sesuai dengan tahun kartu rekening.'
                );
        }
    }

    private function validasiAnggota(
        Validator $validator,
        int $index,
        ?string $field,
        mixed $value
    ): void {
        if (!in_array($field, ['nama', 'agama', 'tanggal_masuk'], true)) {
            $validator->errors()->add("changes.{$index}.field", 'Field anggota tidak valid.');

            return;
        }

        if ($field === 'nama') {
            if (!is_string($value) || trim($value) === '') {
                $validator->errors()->add("changes.{$index}.value", 'Nama anggota harus diisi.');
            }

            return;
        }
        if ($field === 'tanggal_masuk') {
            if (!is_string($value) || trim($value) === '') {
                $validator->errors()->add("changes.{$index}.value", 'Tanggal bergabung harus diisi.');
            }
            return;
        }
        if (!in_array($value, [Anggota::AGAMA_ISLAM, Anggota::AGAMA_NONISLAM], true)) {
            $validator->errors()->add("changes.{$index}.value", 'Agama harus diisi.');
        }
    }

    private function validasiSimpanan(
        Validator $validator,
        int $index,
        ?string $field,
        mixed $value
    ): void {
        if (
            !in_array(
                $field,
                [
                    'simpanan_pokok',
                    'simpanan_wajib',
                    'simpanan_sukarela',
                    'simpanan_hari_raya',
                    'simpanan_rekreasi',
                ],
                true
            )
        ) {
            $validator
                ->errors()
                ->add(
                    "changes.{$index}.field",
                    'Jenis simpanan tidak valid.'
                );

            return;
        }

        if (
            $this->nilaiKosong(
                $value
            )
        ) {
            return;
        }

        if (
            !$this->nominalValid(
                $value
            )
        ) {
            $validator
                ->errors()
                ->add(
                    "changes.{$index}.value",
                    'Nominal simpanan harus berupa angka valid.'
                );
        }
    }

    private function validasiTransaksiPinjaman(
        Validator $validator,
        int $index,
        ?string $action,
        mixed $entryId,
        ?string $field,
        mixed $value
    ): void {
        if (
            $field
            !== 'jumlah'
        ) {
            $validator
                ->errors()
                ->add(
                    "changes.{$index}.field",
                    'Field transaksi pinjaman tidak valid.'
                );

            return;
        }

        if (!$action) {
            $validator
                ->errors()
                ->add(
                    "changes.{$index}.action",
                    'Jenis transaksi harus dipilih.'
                );

            return;
        }

        if (
            in_array(
                $action,
                [
                    'update_pinjaman',
                    'update_angsuran',
                ],
                true
            )
            && !$entryId
        ) {
            $validator
                ->errors()
                ->add(
                    "changes.{$index}.entry_id",
                    'Data transaksi tidak ditemukan.'
                );

            return;
        }

        if (
            $this->nilaiKosong(
                $value
            )
        ) {
            return;
        }

        if (
            !$this->nominalValid(
                $value
            )
        ) {
            $message =
                in_array(
                    $action,
                    [
                        'create_pinjaman',
                        'update_pinjaman',
                    ],
                    true
                )
                ? 'Nominal pinjaman harus berupa angka valid.'
                : 'Nominal angsuran harus berupa angka valid.';

            $validator
                ->errors()
                ->add(
                    "changes.{$index}.value",
                    $message
                );

            return;
        }

        if (
            (float) $value
            < 0
        ) {
            $message =
                in_array(
                    $action,
                    [
                        'create_pinjaman',
                        'update_pinjaman',
                    ],
                    true
                )
                ? 'Nominal pinjaman tidak boleh negatif.'
                : 'Nominal angsuran tidak boleh negatif.';

            $validator
                ->errors()
                ->add(
                    "changes.{$index}.value",
                    $message
                );
        }
    }

    private function normalisasiNominal(
        mixed $value
    ): mixed {
        if (
            !is_string(
                $value
            )
        ) {
            return $value;
        }

        $value =
            trim(
                $value
            );

        if (
            $value
            === ''
        ) {
            return '';
        }

        if (
            preg_match(
                '/^-?\d+$/',
                $value
            )
            === 1
        ) {
            return $value;
        }

        if (
            preg_match(
                '/^-?\d{1,3}(\.\d{3})+$/',
                $value
            )
            === 1
        ) {
            return str_replace(
                '.',
                '',
                $value
            );
        }

        return $value;
    }

    private function nilaiKosong(
        mixed $value
    ): bool {
        return
            $value
            === ''
            || $value
            === null;
    }

    private function nominalValid(
        mixed $value
    ): bool {
        if (
            is_int(
                $value
            )
            || is_float(
                $value
            )
        ) {
            return true;
        }

        if (
            !is_string(
                $value
            )
        ) {
            return false;
        }

        return preg_match(
            '/^-?\d+$/',
            $value
        )
            === 1;
    }
}
