<script setup>
import BaseModal from "@/Components/UI/BaseModal.vue";
import { ref } from "vue";
const page = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    tahun: {
        type: Number,
        required: true,
    },
});
const selectedMonth = ref("");
const radioMonth = [
    { value: 1, label: "Januari" },
    { value: 2, label: "Februari" },
    { value: 3, label: "Maret" },
    { value: 4, label: "April" },
    { value: 5, label: "Mei" },
    { value: 6, label: "Juni" },
    { value: 7, label: "Juli" },
    { value: 8, label: "Agustus" },
    { value: 9, label: "September" },
    { value: 10, label: "Oktober" },
    { value: 11, label: "November" },
    { value: 12, label: "Desember" },
];
function downloadLaporan() {
    const url = route("laporan.simpanan-hari-raya.export", {
        tahun: page.tahun,
        bulan: selectedMonth.value,
    });
    window.open(url, "_blank");
}
</script>

<template>
    <BaseModal :show="page.show" @close="$emit('close')">
        <h3 class="text-xl font-black text-slate-800">
            Pilih Bulan Idul Fitri
        </h3>

        <p class="mt-1 text-sm text-slate-500">
            Pilih bulan idul fitri untuk mencetak laporan simpanan hari raya.
        </p>

        <div class="mt-6 space-y-4">
            <div>
                <p class="text-sm font-bold text-slate-700">Bulan Idul Fitri</p>

                <div class="mt-2 grid grid-cols-2 gap-3">
                    <label
                        v-for="option in radioMonth"
                        :key="option.value"
                        class="flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 transition"
                        :class="
                            selectedMonth === option.value
                                ? 'border-[#1a6fbd] bg-blue-50 text-[#1a6fbd] ring-2 ring-blue-100'
                                : 'border-slate-200 bg-white text-slate-500 hover:border-blue-200 hover:bg-blue-50/40'
                        "
                    >
                        <input
                            type="radio"
                            class="peer sr-only"
                            :value="option.value"
                            v-model="selectedMonth"
                        />

                        {{ option.label }}
                    </label>
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-2">
            <button
                type="button"
                class="rounded-xl px-4 py-2.5 text-sm font-bold text-slate-400 hover:bg-slate-50"
                @click="$emit('close')"
            >
                Batal
            </button>

            <button
                type="button"
                :disabled="!selectedMonth"
                @click="downloadLaporan"
                class="rounded-xl bg-gradient-to-r from-[#1a6fbd] to-[#0f4f8e] px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-blue-200 disabled:opacity-60"
            >
                Download Laporan
            </button>
        </div>
    </BaseModal>
</template>
