<script setup>
import EditableMoneyCell from "@/Components/KartuRekening/EditableMoneyCell.vue";
import LoanEntryCell from "@/Components/KartuRekening/LoanEntryCell.vue";
import EditableTextField from "./EditableTextField.vue";

const props = defineProps({
    member: {
        type: Object,
        required: true,
    },

    dirtyKeys: {
        type: Object,
        default: () => ({}),
    },

    printMode: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    "change",
    "discard",
    "delete",
    "keluarkan",
    "autofill",
]);

const simpananFields = [
    {
        key: "simpanan_pokok",
        label: "SIMPOK",
    },

    {
        key: "simpanan_wajib",
        label: "SIMWA",
    },

    {
        key: "simpanan_sukarela",
        label: "SSR",
    },

    {
        key: "simpanan_hari_raya",
        label: "SHR",
    },

    {
        key: "simpanan_rekreasi",
        label: "SREK",
    },
];

const makeKey = (periode, section, field) => {
    return [props.member.id, periode, section, field].join("|");
};

const isDirty = (periode, section, field) => {
    return Boolean(props.dirtyKeys[makeKey(periode, section, field)]);
};

const emitSimpanan = (row, field, value) => {
    emit("change", {
        anggota_id: props.member.id,

        periode: row.periode,

        section: "simpanan",

        field,

        value,
    });
};

const formatNumber = (value, accounting = false) => {
    if (value === null || value === undefined || Number(value) === 0) {
        return "-";
    }

    const number = Number(value);

    const formatted = new Intl.NumberFormat("id-ID", {
        maximumFractionDigits: 0,
    }).format(Math.abs(number));

    if (number < 0 && accounting) {
        return `(${formatted})`;
    }

    if (number < 0) {
        return `-${formatted}`;
    }

    return formatted;
};

const nominalClass = (value) => {
    return Number(value ?? 0) < 0 ? "text-red-600" : "text-slate-700";
};
const periodeAnggota = () => {
    return props.member.rows[0]?.periode ?? `${new Date().getFullYear()}-01`;
};

const emitNama = (value) => {
    emit("change", {
        anggota_id: props.member.id,
        periode: periodeAnggota(),
        section: "anggota",
        field: "nama",
        value,
    });
};

const emitTanggalMasuk = (value) => {
    emit("change", {
        anggota_id: props.member.id,
        periode: periodeAnggota(),
        section: "anggota",
        field: "tanggal_masuk",

        value: value === "" || value === undefined ? null : value,
    });
};

const emitAgama = (value) => {
    emit("change", {
        anggota_id: props.member.id,

        periode:
            props.member.rows[0]?.periode ?? `${new Date().getFullYear()}-01`,

        section: "anggota",
        field: "agama",
        value: value === "" ? null : value,
    });
};
</script>

<template>
    <article
        class="print-sheet overflow-hidden rounded-3xl border border-blue-100 bg-white shadow-[0_8px_28px_rgba(26,111,189,0.08)]"
    >
        <header
            class="flex flex-col gap-4 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-green-50 px-5 py-4 lg:flex-row lg:items-center lg:justify-between"
        >
            <div>
                <p
                    class="text-xs font-black uppercase tracking-[0.14em] text-[#3aab2e]"
                >
                    No. {{ member.nomor_anggota }}
                </p>

                <div class="mt-1">
                    <EditableTextField
                        :model-value="member.nama"
                        :dirty="
                            isDirty(member.rows[0]?.periode, 'anggota', 'nama')
                        "
                        :readonly="printMode"
                        @change="emitNama"
                    />
                </div>

                <div
                    class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-400"
                >
                    <span>Bergabung:</span>

                    <EditableTextField
                        :model-value="member.tanggal_masuk"
                        input-type="date"
                        nullable
                        empty-label="-"
                        :dirty="
                            isDirty(
                                member.rows[0]?.periode,
                                'anggota',
                                'tanggal_masuk',
                            )
                        "
                        :readonly="printMode"
                        @change="emitTanggalMasuk"
                    />

                    <span>· Status:</span>

                    <span class="font-bold capitalize">
                        {{ member.status }}
                    </span>

                    <span
                        v-if="
                            isDirty(
                                member.rows[0]?.periode,
                                'anggota',
                                'tanggal_masuk',
                            )
                        "
                        class="rounded-lg bg-orange-50 px-2 py-1 text-[11px] font-bold text-orange-600"
                    >
                        Belum disimpan
                    </span>
                </div>
                <div class="no-print mt-3 flex flex-wrap items-center gap-2">
                    <label
                        :for="`agama-${member.id}`"
                        class="text-xs font-bold text-slate-500"
                    >
                        Agama:
                    </label>

                    <select
                        :id="`agama-${member.id}`"
                        :value="member.agama ?? ''"
                        class="min-w-[150px] cursor-pointer rounded-xl border bg-white px-3 py-2 text-xs font-bold outline-none transition focus:border-[#1a6fbd] focus:ring-2 focus:ring-blue-100"
                        :class="
                            isDirty(member.rows[0]?.periode, 'anggota', 'agama')
                                ? 'border-orange-300 bg-orange-50 text-orange-700 ring-2 ring-orange-100'
                                : 'border-slate-200 text-slate-700 hover:border-blue-300'
                        "
                        @change="emitAgama($event.target.value)"
                    >
                        <option value="">--</option>

                        <option value="islam">Islam</option>

                        <option value="nonislam">Non-Islam</option>
                    </select>

                    <span
                        v-if="
                            isDirty(member.rows[0]?.periode, 'anggota', 'agama')
                        "
                        class="rounded-lg bg-orange-50 px-2 py-1 text-[11px] font-bold text-orange-600"
                    >
                        Belum disimpan
                    </span>
                </div>
            </div>

            <div v-if="!printMode" class="no-print flex flex-wrap gap-2">
                <button
                    v-if="member.status === 'aktif'"
                    type="button"
                    class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-200"
                    @click="$emit('keluarkan', member)"
                >
                    Keluarkan
                </button>

                <button
                    type="button"
                    class="rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-red-500 transition hover:bg-red-100"
                    @click="$emit('delete', member)"
                >
                    Hapus
                </button>
            </div>
        </header>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1580px] border-collapse text-xs">
                <thead>
                    <tr
                        class="bg-gradient-to-r from-[#0f4f8e] via-[#1a6fbd] to-[#3aab2e] text-white"
                    >
                        <th
                            rowspan="2"
                            class="border border-white/20 px-3 py-3 text-left"
                        >
                            Bulan
                        </th>

                        <th
                            colspan="6"
                            class="border border-white/20 px-3 py-3 text-center"
                        >
                            Simpanan
                        </th>

                        <th
                            colspan="4"
                            class="border border-white/20 px-3 py-3 text-center"
                        >
                            Pinjaman Reguler
                        </th>

                        <th
                            colspan="4"
                            class="border border-white/20 px-3 py-3 text-center"
                        >
                            Pinjaman Sebrak
                        </th>

                        <th
                            rowspan="2"
                            class="border border-white/20 px-3 py-3 text-right"
                        >
                            Jumlah
                            <br />
                            Tagihan
                        </th>
                    </tr>

                    <tr class="bg-[#1a6fbd] text-white">
                        <th
                            v-for="field in simpananFields"
                            :key="field.key"
                            class="border border-white/20 px-3 py-2"
                        >
                            {{ field.label }}
                        </th>

                        <th class="border border-white/20 px-3 py-2">
                            Jumlah Simpanan
                        </th>

                        <th class="border border-white/20 px-3 py-2">Ke</th>

                        <th class="border border-white/20 px-3 py-2">Jumlah</th>

                        <th class="border border-white/20 px-3 py-2">Sisa</th>

                        <th class="border border-white/20 px-3 py-2">Jasa</th>

                        <th class="border border-white/20 px-3 py-2">Ke</th>

                        <th class="border border-white/20 px-3 py-2">Jumlah</th>

                        <th class="border border-white/20 px-3 py-2">Sisa</th>

                        <th class="border border-white/20 px-3 py-2">Jasa</th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="row in member.rows"
                        :key="row.periode"
                        class="transition hover:bg-blue-50/60"
                    >
                        <td
                            class="cursor-pointer border border-blue-100 bg-blue-50/40 px-3 py-2 font-black text-[#1a6fbd] transition hover:bg-blue-100"
                            @click="$emit('autofill', member.id, row.periode)"
                        >
                            {{ row.bulan }}
                        </td>

                        <td
                            v-for="field in simpananFields"
                            :key="field.key"
                            class="border border-blue-100 p-1"
                        >
                            <EditableMoneyCell
                                :model-value="row.simpanan[field.key]"
                                :dirty="
                                    isDirty(row.periode, 'simpanan', field.key)
                                "
                                :readonly="printMode"
                                allow-negative
                                @change="emitSimpanan(row, field.key, $event)"
                            />
                        </td>

                        <td
                            class="border border-blue-100 bg-green-50/50 px-3 py-2 text-right font-bold tabular-nums"
                            :class="nominalClass(row.simpanan.jumlah_simpanan)"
                        >
                            {{ formatNumber(row.simpanan.jumlah_simpanan) }}
                        </td>

                        <td
                            class="border border-blue-100 px-3 py-2 text-center"
                        >
                            {{ row.reguler.ke ?? "-" }}
                        </td>

                        <td class="border border-blue-100 p-1">
                            <LoanEntryCell
                                :loan="row.reguler"
                                :anggota-id="member.id"
                                :periode="row.periode"
                                section="reguler"
                                :dirty-keys="dirtyKeys"
                                :print-mode="printMode"
                                @change="$emit('change', $event)"
                                @discard="$emit('discard', $event)"
                            />
                        </td>

                        <td
                            class="border border-blue-100 px-3 py-2 text-right tabular-nums"
                        >
                            {{ formatNumber(row.reguler.sisa) }}
                        </td>

                        <td
                            class="border border-blue-100 px-3 py-2 text-right tabular-nums"
                        >
                            {{ formatNumber(row.reguler.jasa) }}
                        </td>

                        <td
                            class="border border-blue-100 px-3 py-2 text-center"
                        >
                            {{ row.sebrak.ke ?? "-" }}
                        </td>

                        <td class="border border-blue-100 p-1">
                            <LoanEntryCell
                                :loan="row.sebrak"
                                :anggota-id="member.id"
                                :periode="row.periode"
                                section="sebrak"
                                :dirty-keys="dirtyKeys"
                                :print-mode="printMode"
                                @change="$emit('change', $event)"
                                @discard="$emit('discard', $event)"
                            />
                        </td>

                        <td
                            class="border border-blue-100 px-3 py-2 text-right tabular-nums"
                        >
                            {{ formatNumber(row.sebrak.sisa) }}
                        </td>

                        <td
                            class="border border-blue-100 px-3 py-2 text-right tabular-nums"
                        >
                            {{ formatNumber(row.sebrak.jasa) }}
                        </td>

                        <td
                            class="border border-blue-100 bg-orange-50/50 px-3 py-2 text-right font-black tabular-nums"
                            :class="nominalClass(row.jumlah_tagihan)"
                        >
                            {{ formatNumber(row.jumlah_tagihan, true) }}
                        </td>
                    </tr>
                </tbody>

                <tfoot>
                    <tr class="bg-slate-50 font-black text-slate-700">
                        <td class="border border-blue-100 px-3 py-3">JUMLAH</td>

                        <td
                            class="border border-blue-100 px-3 py-3 text-right tabular-nums"
                        >
                            {{ formatNumber(member.totals.simpanan_pokok) }}
                        </td>

                        <td
                            class="border border-blue-100 px-3 py-3 text-right tabular-nums"
                        >
                            {{ formatNumber(member.totals.simpanan_wajib) }}
                        </td>

                        <td
                            class="border border-blue-100 px-3 py-3 text-right tabular-nums"
                        >
                            {{ formatNumber(member.totals.simpanan_sukarela) }}
                        </td>

                        <td
                            class="border border-blue-100 px-3 py-3 text-right tabular-nums"
                        >
                            {{ formatNumber(member.totals.simpanan_hari_raya) }}
                        </td>

                        <td
                            class="border border-blue-100 px-3 py-3 text-right tabular-nums"
                        >
                            {{ formatNumber(member.totals.simpanan_rekreasi) }}
                        </td>

                        <td
                            class="border border-blue-100 bg-green-100/60 px-3 py-3 text-right tabular-nums"
                        >
                            {{ formatNumber(member.totals.total_simpanan) }}
                        </td>

                        <td
                            colspan="3"
                            class="border border-blue-100 px-3 py-3 text-center"
                        >
                            Jumlah Jasa Reguler
                        </td>

                        <td
                            class="border border-blue-100 px-3 py-3 text-right tabular-nums"
                        >
                            {{ formatNumber(member.totals.jasa_reguler) }}
                        </td>

                        <td
                            colspan="3"
                            class="border border-blue-100 px-3 py-3 text-center"
                        >
                            Jumlah Jasa Sebrak
                        </td>

                        <td
                            class="border border-blue-100 px-3 py-3 text-right tabular-nums"
                        >
                            {{ formatNumber(member.totals.jasa_sebrak) }}
                        </td>

                        <td class="border border-blue-100" />
                    </tr>
                </tfoot>
            </table>
        </div>
    </article>
</template>
