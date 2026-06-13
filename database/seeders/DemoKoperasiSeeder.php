<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Angsuran;
use App\Models\Pinjaman;
use App\Models\RekapSimpanan;
use App\Models\ShuAnggota;
use App\Models\Simpanan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoKoperasiSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $admin = $this->buatAdmin();

            $this->buatPengurus();

            $bulanSekarang = CarbonImmutable::now()
                ->startOfMonth();

            $sparxie = $this->buatAnggota(
                nomorAnggota: '001',
                nama: 'sparxie',
                agama: 'islam',
                tanggalMasuk: $bulanSekarang
                    ->subYears(2)
                    ->startOfYear()
            );

            $yaoguang = $this->buatAnggota(
                nomorAnggota: '002',
                nama: 'yaoguang',
                agama: 'islam',
                tanggalMasuk: $bulanSekarang
                    ->subYears(1)
                    ->startOfYear()
            );
            for ($i = 3; $i <= 100; $i++) {
                $this->buatAnggota(
                    nomorAnggota: str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                    nama: "Anggota {$i}",
                    agama: random_int(0, 1) === 0 ? Anggota::AGAMA_ISLAM : Anggota::AGAMA_NONISLAM,
                    tanggalMasuk: $bulanSekarang
                        ->subYears(1)
                        ->startOfYear()
                );
            }
            $this->buatSimpanansparxie(
                anggota: $sparxie,
                pencatat: $admin,
                bulanSekarang: $bulanSekarang
            );

            $this->buatSimpananyaoguang(
                anggota: $yaoguang,
                pencatat: $admin,
                bulanSekarang: $bulanSekarang
            );

            $this->perbaruiRekapSimpanan(
                $sparxie
            );

            $this->perbaruiRekapSimpanan(
                $yaoguang
            );

            $this->buatPinjamanRegulersparxie(
                anggota: $sparxie,
                pencatat: $admin,
                bulanSekarang: $bulanSekarang
            );

            $this->buatPinjamanSebrakyaoguang(
                anggota: $yaoguang,
                pencatat: $admin,
                bulanSekarang: $bulanSekarang
            );

            $this->perbaruiShu(
                $sparxie
            );

            $this->perbaruiShu(
                $yaoguang
            );
        });
    }

    /**
     * Membuat akun admin untuk login.
     */
    private function buatAdmin(): User
    {
        return User::updateOrCreate(
            [
                'username' => 'admin',
            ],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => Hash::make(
                    'admin12345'
                ),
                'role' => User::ROLE_ADMIN,
                'must_change_password' => false,
            ]
        );
    }

    /**
     * Membuat satu akun pengurus tambahan.
     */
    private function buatPengurus(): User
    {
        return User::updateOrCreate(
            [
                'username' => 'pengurus',
            ],
            [
                'name' => 'Pengurus Koperasi',
                'username' => 'pengurus',
                'password' => Hash::make(
                    'pengurus12345'
                ),
                'role' => User::ROLE_PENGURUS,
                'must_change_password' => false,
            ]
        );
    }

    /**
     * Membuat anggota koperasi.
     */
    private function buatAnggota(
        string $nomorAnggota,
        string $nama,
        string $agama,
        CarbonImmutable $tanggalMasuk
    ): Anggota {
        return Anggota::updateOrCreate(
            [
                'nomor_anggota' =>
                $nomorAnggota,
            ],
            [
                'nama' => $nama,
                'agama' => $agama,
                'tanggal_masuk' =>
                $tanggalMasuk,

                'tanggal_keluar' => null,
                'status' =>
                Anggota::STATUS_AKTIF,
            ]
        );
    }

    /**
     * Simpanan anggota pertama selama empat bulan.
     */
    private function buatSimpanansparxie(
        Anggota $anggota,
        User $pencatat,
        CarbonImmutable $bulanSekarang
    ): void {
        $this->simpanSimpanan(
            anggota: $anggota,
            pencatat: $pencatat,
            periode: $bulanSekarang
                ->subMonths(3),
            simpananPokok: 100_000,
            simpananWajib: 50_000,
            simpananSukarela: 25_000,
            simpananHariRaya: 100_000,
            simpananRekreasi: 0,
            keterangan: 'Simpanan awal anggota'
        );

        $this->simpanSimpanan(
            anggota: $anggota,
            pencatat: $pencatat,
            periode: $bulanSekarang
                ->subMonths(2),
            simpananPokok: 0,
            simpananWajib: 50_000,
            simpananSukarela: 25_000,
            simpananHariRaya: 100_000,
            simpananRekreasi: 50_000
        );

        $this->simpanSimpanan(
            anggota: $anggota,
            pencatat: $pencatat,
            periode: $bulanSekarang
                ->subMonth(),
            simpananPokok: 0,
            simpananWajib: 50_000,
            simpananSukarela: 25_000,
            simpananHariRaya: 100_000,
            simpananRekreasi: 50_000
        );

        $this->simpanSimpanan(
            anggota: $anggota,
            pencatat: $pencatat,
            periode: $bulanSekarang,
            simpananPokok: 0,
            simpananWajib: 50_000,
            simpananSukarela: 25_000,
            simpananHariRaya: 100_000,
            simpananRekreasi: 50_000
        );
    }

    /**
     * Simpanan anggota kedua selama empat bulan.
     *
     * Terdapat contoh penarikan SHR sebesar Rp200.000
     * pada periode bulan lalu.
     */
    private function buatSimpananyaoguang(
        Anggota $anggota,
        User $pencatat,
        CarbonImmutable $bulanSekarang
    ): void {
        $this->simpanSimpanan(
            anggota: $anggota,
            pencatat: $pencatat,
            periode: $bulanSekarang
                ->subMonths(3),
            simpananPokok: 100_000,
            simpananWajib: 75_000,
            simpananSukarela: 50_000,
            simpananHariRaya: 100_000,
            simpananRekreasi: 0,
            keterangan: 'Simpanan awal anggota'
        );

        $this->simpanSimpanan(
            anggota: $anggota,
            pencatat: $pencatat,
            periode: $bulanSekarang
                ->subMonths(2),
            simpananPokok: 0,
            simpananWajib: 75_000,
            simpananSukarela: 50_000,
            simpananHariRaya: 100_000,
            simpananRekreasi: 0
        );

        $this->simpanSimpanan(
            anggota: $anggota,
            pencatat: $pencatat,
            periode: $bulanSekarang
                ->subMonth(),
            simpananPokok: 0,
            simpananWajib: 75_000,
            simpananSukarela: 50_000,
            simpananHariRaya: -200_000,
            simpananRekreasi: 0,
            keterangan: 'Penarikan simpanan hari raya'
        );

        $this->simpanSimpanan(
            anggota: $anggota,
            pencatat: $pencatat,
            periode: $bulanSekarang,
            simpananPokok: 0,
            simpananWajib: 75_000,
            simpananSukarela: 50_000,
            simpananHariRaya: 100_000,
            simpananRekreasi: 0
        );
    }

    /**
     * Menyimpan satu transaksi simpanan bulanan.
     */
    private function simpanSimpanan(
        Anggota $anggota,
        User $pencatat,
        CarbonImmutable $periode,
        int $simpananPokok,
        int $simpananWajib,
        int $simpananSukarela,
        int $simpananHariRaya,
        int $simpananRekreasi,
        ?string $keterangan = null
    ): Simpanan {
        $jumlahSimpanan =
            $simpananPokok
            + $simpananWajib
            + $simpananSukarela
            + $simpananHariRaya
            + $simpananRekreasi;

        return Simpanan::updateOrCreate(
            [
                'anggota_id' =>
                $anggota->id,
                'periode' =>
                $periode->toDateString(),
            ],
            [
                'simpanan_pokok' =>
                $simpananPokok,
                'simpanan_wajib' =>
                $simpananWajib,
                'simpanan_sukarela' =>
                $simpananSukarela,
                'simpanan_hari_raya' =>
                $simpananHariRaya,
                'simpanan_rekreasi' =>
                $simpananRekreasi,
                'jumlah_simpanan' =>
                $jumlahSimpanan,
                'keterangan' =>
                $keterangan,
                'created_by' =>
                $pencatat->id,
            ]
        );
    }

    /**
     * Menghitung ulang rekap simpanan anggota.
     */
    private function perbaruiRekapSimpanan(
        Anggota $anggota
    ): RekapSimpanan {
        $query = Simpanan::query()
            ->where(
                'anggota_id',
                $anggota->id
            );

        $totalPokok = (float)
        (clone $query)->sum(
            'simpanan_pokok'
        );

        $totalWajib = (float)
        (clone $query)->sum(
            'simpanan_wajib'
        );

        $totalSukarela = (float)
        (clone $query)->sum(
            'simpanan_sukarela'
        );

        $totalHariRaya = (float)
        (clone $query)->sum(
            'simpanan_hari_raya'
        );

        $totalRekreasi = (float)
        (clone $query)->sum(
            'simpanan_rekreasi'
        );

        return RekapSimpanan::updateOrCreate(
            [
                'anggota_id' =>
                $anggota->id,
            ],
            [
                'total_simpanan_pokok' =>
                $totalPokok,
                'total_simpanan_wajib' =>
                $totalWajib,
                'total_simpanan_sukarela' =>
                $totalSukarela,
                'total_simpanan_hari_raya' =>
                $totalHariRaya,
                'total_simpanan_rekreasi' =>
                $totalRekreasi,
                'total_simpanan' =>
                $totalPokok
                    + $totalWajib
                    + $totalSukarela
                    + $totalHariRaya
                    + $totalRekreasi,
            ]
        );
    }

    /**
     * Pinjaman reguler anggota pertama.
     *
     * Saldo awal    : Rp20.000.000
     * Angsuran pokok: Rp1.500.000 per bulan
     * Jasa          : 1,5% dari saldo awal periode
     */
    private function buatPinjamanRegulersparxie(
        Anggota $anggota,
        User $pencatat,
        CarbonImmutable $bulanSekarang
    ): void {
        $tanggalPinjaman =
            $bulanSekarang
            ->subMonths(3);

        $pinjaman = Pinjaman::updateOrCreate(
            [
                'anggota_id' =>
                $anggota->id,
                'jenis_pinjaman' =>
                Pinjaman::JENIS_REGULER,
                'tanggal_pinjaman' =>
                $tanggalPinjaman
                    ->toDateString(),
            ],
            [
                'nominal_pinjaman' =>
                20_000_000,
                'persentase_jasa' =>
                1.50,
                'sisa_pinjaman' =>
                15_500_000,
                'status' =>
                Pinjaman::STATUS_AKTIF,
                'keterangan' =>
                'Pinjaman reguler demo',
                'created_by' =>
                $pencatat->id,
            ]
        );

        $this->simpanAngsuran(
            pinjaman: $pinjaman,
            pencatat: $pencatat,
            periode: $bulanSekarang
                ->subMonths(2),
            angsuranKe: 1,
            saldoAwal: 20_000_000,
            nominalAngsuran: 1_500_000
        );

        $this->simpanAngsuran(
            pinjaman: $pinjaman,
            pencatat: $pencatat,
            periode: $bulanSekarang
                ->subMonth(),
            angsuranKe: 2,
            saldoAwal: 18_500_000,
            nominalAngsuran: 1_500_000
        );

        $this->simpanAngsuran(
            pinjaman: $pinjaman,
            pencatat: $pencatat,
            periode: $bulanSekarang,
            angsuranKe: 3,
            saldoAwal: 17_000_000,
            nominalAngsuran: 1_500_000
        );

        $pinjaman->update([
            'sisa_pinjaman' =>
            15_500_000,
            'status' =>
            Pinjaman::STATUS_AKTIF,
        ]);
    }

    /**
     * Pinjaman sebrak anggota kedua.
     *
     * Saldo awal: Rp5.000.000
     * Angsuran  : belum mengurangi pokok
     * Jasa      : 2% dari saldo awal periode
     */
    private function buatPinjamanSebrakyaoguang(
        Anggota $anggota,
        User $pencatat,
        CarbonImmutable $bulanSekarang
    ): void {
        $tanggalPinjaman =
            $bulanSekarang
            ->subMonths(2);

        $pinjaman = Pinjaman::updateOrCreate(
            [
                'anggota_id' =>
                $anggota->id,
                'jenis_pinjaman' =>
                Pinjaman::JENIS_SEBRAK,
                'tanggal_pinjaman' =>
                $tanggalPinjaman
                    ->toDateString(),
            ],
            [
                'nominal_pinjaman' =>
                5_000_000,
                'persentase_jasa' =>
                2.00,
                'sisa_pinjaman' =>
                5_000_000,
                'status' =>
                Pinjaman::STATUS_AKTIF,
                'keterangan' =>
                'Pinjaman sebrak demo',
                'created_by' =>
                $pencatat->id,
            ]
        );

        $this->simpanAngsuran(
            pinjaman: $pinjaman,
            pencatat: $pencatat,
            periode: $bulanSekarang
                ->subMonth(),
            angsuranKe: null,
            saldoAwal: 5_000_000,
            nominalAngsuran: 0
        );

        $this->simpanAngsuran(
            pinjaman: $pinjaman,
            pencatat: $pencatat,
            periode: $bulanSekarang,
            angsuranKe: null,
            saldoAwal: 5_000_000,
            nominalAngsuran: 0
        );

        $pinjaman->update([
            'sisa_pinjaman' =>
            5_000_000,
            'status' =>
            Pinjaman::STATUS_AKTIF,
        ]);
    }

    /**
     * Menyimpan pembayaran angsuran serta perhitungan jasa.
     */
    private function simpanAngsuran(
        Pinjaman $pinjaman,
        User $pencatat,
        CarbonImmutable $periode,
        ?int $angsuranKe,
        int $saldoAwal,
        int $nominalAngsuran
    ): Angsuran {
        $persentaseJasa =
            (float)
            $pinjaman
                ->persentase_jasa;

        $jasaPinjaman =
            $saldoAwal
            * (
                $persentaseJasa
                / 100
            );

        $sisaPinjaman =
            $saldoAwal
            - $nominalAngsuran;

        $jumlahTagihan =
            $nominalAngsuran
            + $jasaPinjaman;

        return Angsuran::updateOrCreate(
            [
                'pinjaman_id' =>
                $pinjaman->id,
                'periode' =>
                $periode->toDateString(),
            ],
            [
                'tanggal_pembayaran' =>
                $periode
                    ->addDays(5)
                    ->toDateString(),
                'angsuran_ke' =>
                $angsuranKe,
                'saldo_awal' =>
                $saldoAwal,
                'nominal_angsuran' =>
                $nominalAngsuran,
                'persentase_jasa' =>
                $persentaseJasa,
                'jasa_pinjaman' =>
                $jasaPinjaman,
                'sisa_pinjaman' =>
                $sisaPinjaman,
                'jumlah_tagihan' =>
                $jumlahTagihan,
                'keterangan' =>
                $nominalAngsuran === 0
                    ? 'Pembayaran jasa pinjaman'
                    : 'Pembayaran angsuran pinjaman',
                'created_by' =>
                $pencatat->id,
            ]
        );
    }

    /**
     * Membuat gambaran rekap SHU tahun berjalan.
     *
     * Sesuai keputusan desain:
     * - 50% berdasarkan total simpanan;
     * - 50% berdasarkan total pinjaman.
     */
    private function perbaruiShu(
        Anggota $anggota
    ): ShuAnggota {
        $totalSimpanan = (float)
        RekapSimpanan::query()
            ->where(
                'anggota_id',
                $anggota->id
            )
            ->value(
                'total_simpanan'
            );

        $totalJasaPinjaman = (float)
        Angsuran::query()
            ->whereHas(
                'pinjaman',
                function (
                    $query
                ) use (
                    $anggota
                ): void {
                    $query->where(
                        'anggota_id',
                        $anggota->id
                    );
                }
            )
            ->sum(
                'jasa_pinjaman'
            );

        $shuSimpanan =
            $totalSimpanan
            * 0.50;

        $shuPinjaman =
            $totalJasaPinjaman
            * 0.50;

        return ShuAnggota::updateOrCreate(
            [
                'anggota_id' =>
                $anggota->id,

                'tahun' =>
                now()->year,
            ],
            [
                'total_simpanan' =>
                $totalSimpanan,

                'total_jasa_pinjaman' =>
                $totalJasaPinjaman,

                'persentase_simpanan' =>
                50,

                'persentase_jasa_pinjaman' =>
                50,

                'shu_simpanan' =>
                $shuSimpanan,

                'shu_pinjaman' =>
                $shuPinjaman,

                'total_shu' =>
                $shuSimpanan
                    + $shuPinjaman,

                'calculated_at' =>
                now(),
            ]
        );
    }
}
