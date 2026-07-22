<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/UI/Pagination.vue";
import { Head, router } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, ref, watch } from "vue";
import Reveal from "@/Components/UI/Reveal.vue";

const props = defineProps({
    histories: {
        type: Array,
        default: () => [],
    },

    members: {
        type: Array,
        default: () => [],
    },

    filters: {
        type: Object,
        required: true,
    },

    years: {
        type: Array,
        default: () => [],
    },
});

const perPage = 10;

const months = [
    { value: "", label: "Semua Bulan" },
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

const transactionTypes = [
    { value: "", label: "Semua Transaksi" },
    { value: "simpanan", label: "Simpanan" },
    { value: "pinjaman", label: "Pinjaman" },
    { value: "angsuran", label: "Angsuran" },
];

const search = ref("");
const currentPage = ref(1);
const selectedYear = ref(props.filters.tahun);
const selectedMonth = ref(props.filters.bulan ?? "");
const selectedMember = ref(props.filters.anggota_id ?? "");
const selectedType = ref(props.filters.jenis ?? "");
const loading = ref(false);

let filterTimer = null;

const filteredHistories = computed(() => {
    const keyword = search.value.trim().toLowerCase();

    if (!keyword) {
        return props.histories;
    }

    return props.histories.filter((history) => {
        return [
            history.nomor_anggota,
            history.nama,
            history.jenis_label,
            history.rincian,
            history.keterangan,
            history.periode,
        ].some((value) =>
            String(value ?? "")
                .toLowerCase()
                .includes(keyword),
        );
    });
});

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(filteredHistories.value.length / perPage));
});

const paginatedHistories = computed(() => {
    const start = (currentPage.value - 1) * perPage;

    return filteredHistories.value.slice(start, start + perPage);
});

const pagination = computed(() => {
    const total = filteredHistories.value.length;
    const from = total === 0 ? 0 : (currentPage.value - 1) * perPage + 1;
    const to = Math.min(currentPage.value * perPage, total);

    return {
        current_page: currentPage.value,
        last_page: totalPages.value,
        from,
        to,
        total,
    };
});

const totalNominal = computed(() => {
    return filteredHistories.value.reduce((total, history) => {
        return total + Number(history.nominal ?? 0);
    }, 0);
});

const totalJasa = computed(() => {
    return filteredHistories.value.reduce((total, history) => {
        return total + Number(history.jasa ?? 0);
    }, 0);
});

watch(search, () => {
    currentPage.value = 1;
});

watch(totalPages, (pages) => {
    if (currentPage.value > pages) {
        currentPage.value = pages;
    }
});

watch([selectedYear, selectedMonth, selectedMember, selectedType], () => {
    currentPage.value = 1;

    clearTimeout(filterTimer);

    filterTimer = setTimeout(() => {
        reloadHistories();
    }, 250);
});

onBeforeUnmount(() => {
    clearTimeout(filterTimer);
});

const reloadHistories = () => {
    loading.value = true;

    router.get(
        route("riwayat.index"),
        {
            tahun: selectedYear.value,
            bulan: selectedMonth.value || undefined,
            anggota_id: selectedMember.value || undefined,
            jenis: selectedType.value || undefined,
        },
        {
            preserveState: false,
            preserveScroll: true,
            replace: true,

            onFinish: () => {
                loading.value = false;
            },
        },
    );
};

const resetFilters = () => {
    selectedYear.value = new Date().getFullYear();
    selectedMonth.value = "";
    selectedMember.value = "";
    selectedType.value = "";
    search.value = "";
};

const changePage = (page) => {
    if (page < 1 || page > totalPages.value || page === currentPage.value) {
        return;
    }

    currentPage.value = page;
};

const formatNumber = (value) => {
    if (value === null || value === undefined) {
        return "-";
    }

    const number = Number(value);

    if (number === 0) {
        return "-";
    }

    return new Intl.NumberFormat("id-ID", {
        maximumFractionDigits: 0,
    }).format(number);
};

const formatDate = (value) => {
    if (!value) {
        return "-";
    }

    return new Intl.DateTimeFormat("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    }).format(new Date(`${value}T00:00:00`));
};

const badgeClass = (type) => {
    if (type === "simpanan") {
        return "bg-green-50 text-green-700 ring-green-100";
    }

    if (type === "pinjaman") {
        return "bg-blue-50 text-blue-700 ring-blue-100";
    }

    return "bg-orange-50 text-orange-700 ring-orange-100";
};
</script>

<template>
    <Head title="Riwayat Transaksi — DigiSejahtera" />

    <AuthenticatedLayout>
        <template #title> Riwayat Transaksi </template>
        <Reveal direction="down" :duration="700">
            <!-- Hero dashboard -->
            <section
                class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0f4f8e] via-[#1a6fbd] to-[#3aab2e] px-6 py-7 text-white shadow-xl shadow-blue-200/70 sm:px-8"
            >
                <div
                    class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-white/10"
                />
                <div
                    class="absolute -bottom-20 right-28 h-44 w-44 rounded-full bg-white/5"
                />

                <div class="absolute right-8 top-7 hidden gap-2 sm:flex">
                    <span
                        class="h-2 w-2 animate-pulse rounded-sm bg-white/40"
                    />
                    <span
                        class="h-2 w-2 animate-pulse rounded-sm bg-white/60 [animation-delay:200ms]"
                    />
                    <span
                        class="h-2 w-2 animate-pulse rounded-sm bg-white/80 [animation-delay:400ms]"
                    />
                </div>

                <div class="relative">
                    <p
                        class="text-xs font-bold uppercase tracking-[0.2em] text-blue-100"
                    >
                        Administrasi Koperasi
                    </p>

                    <h2
                        class="mt-2 text-2xl font-black tracking-tight sm:text-3xl"
                    >
                        Riwayat Transaksi Anggota
                    </h2>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-50/90">
                        Tampilkan histori simpanan, pencairan pinjaman, serta
                        pembayaran angsuran anggota berdasarkan periode
                        tertentu.
                    </p>
                </div>
            </section>
        </Reveal>
        <Reveal direction="up" :duration="700">
            <section
                class="mt-6 rounded-3xl border border-blue-100 bg-white p-4 shadow-[0_8px_28px_rgba(26,111,189,0.08)]"
            >
                <div
                    class="grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(220px,1.3fr)_repeat(3,minmax(150px,1fr))_auto]"
                >
                    <select
                        v-model="selectedMember"
                        class="rounded-xl border border-blue-100 bg-[#f8fbff] px-4 py-3 text-sm font-semibold text-slate-600 outline-none transition focus:border-[#1a6fbd] focus:bg-white focus:ring-4 focus:ring-blue-100"
                    >
                        <option value="">Semua Anggota</option>

                        <option
                            v-for="member in members"
                            :key="member.id"
                            :value="member.id"
                        >
                            {{ member.nomor_anggota }} — {{ member.nama }}
                        </option>
                    </select>

                    <select
                        v-model="selectedMonth"
                        class="rounded-xl border border-blue-100 bg-[#f8fbff] px-4 py-3 text-sm font-semibold text-slate-600 outline-none transition focus:border-[#1a6fbd] focus:bg-white focus:ring-4 focus:ring-blue-100"
                    >
                        <option
                            v-for="month in months"
                            :key="month.value"
                            :value="month.value"
                        >
                            {{ month.label }}
                        </option>
                    </select>

                    <select
                        v-model="selectedYear"
                        class="rounded-xl border border-blue-100 bg-[#f8fbff] px-4 py-3 text-sm font-semibold text-slate-600 outline-none transition focus:border-[#1a6fbd] focus:bg-white focus:ring-4 focus:ring-blue-100"
                    >
                        <option v-for="year in years" :key="year" :value="year">
                            {{ year }}
                        </option>
                    </select>

                    <select
                        v-model="selectedType"
                        class="rounded-xl border border-blue-100 bg-[#f8fbff] px-4 py-3 text-sm font-semibold text-slate-600 outline-none transition focus:border-[#1a6fbd] focus:bg-white focus:ring-4 focus:ring-blue-100"
                    >
                        <option
                            v-for="type in transactionTypes"
                            :key="type.value"
                            :value="type.value"
                        >
                            {{ type.label }}
                        </option>
                    </select>

                    <button
                        type="button"
                        class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-bold text-[#1a6fbd] transition hover:bg-blue-100"
                        @click="resetFilters"
                    >
                        Reset Filter
                    </button>
                </div>

                <div
                    class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Cari nama atau nomor anggota..."
                        class="w-full rounded-xl border border-blue-100 bg-[#f8fbff] px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#1a6fbd] focus:bg-white focus:ring-4 focus:ring-blue-100 sm:w-80"
                    />

                    <p v-if="loading" class="text-xs font-bold text-[#1a6fbd]">
                        Memuat data...
                    </p>
                </div>
            </section>
        </Reveal>

        <section
            v-if="members.length === 0"
            class="mt-5 rounded-3xl border border-blue-100 bg-white py-16 text-center shadow-[0_8px_28px_rgba(26,111,189,0.08)]"
        >
            <Reveal direction="right" :duration="700">
                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-2xl"
                >
                    👥
                </div>

                <p class="mt-4 text-sm font-bold text-slate-600">
                    Data anggota tidak ditemukan
                </p>
            </Reveal>
        </section>

        <template v-else>
            <Reveal direction="right" :duration="700">
                <section class="mt-5 grid gap-3 md:grid-cols-3">
                    <article
                        class="rounded-2xl border border-blue-100 bg-white px-5 py-4 shadow-[0_8px_28px_rgba(26,111,189,0.06)]"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-slate-400"
                        >
                            Jumlah Transaksi
                        </p>

                        <p class="mt-2 text-xl font-black text-[#1a6fbd]">
                            {{ filteredHistories.length }}
                        </p>
                    </article>

                    <article
                        class="rounded-2xl border border-green-100 bg-white px-5 py-4 shadow-[0_8px_28px_rgba(26,111,189,0.06)]"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-slate-400"
                        >
                            Total Nominal
                        </p>

                        <p class="mt-2 text-xl font-black text-green-600">
                            Rp {{ formatNumber(totalNominal) }}
                        </p>
                    </article>

                    <article
                        class="rounded-2xl border border-orange-100 bg-white px-5 py-4 shadow-[0_8px_28px_rgba(26,111,189,0.06)]"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-slate-400"
                        >
                            Total Jasa Pinjaman
                        </p>

                        <p class="mt-2 text-xl font-black text-orange-600">
                            Rp {{ formatNumber(totalJasa) }}
                        </p>
                    </article>
                </section>
            </Reveal>
            <Reveal direction="left" :duration="700">
                <section
                    class="mt-5 overflow-hidden rounded-3xl border border-blue-100 bg-white shadow-[0_8px_28px_rgba(26,111,189,0.08)]"
                >
                    <div class="overflow-x-auto">
                        <table
                            class="w-full min-w-[1180px] border-collapse text-xs"
                        >
                            <thead>
                                <tr
                                    class="bg-gradient-to-r from-[#0f4f8e] via-[#1a6fbd] to-[#3aab2e] text-white"
                                >
                                    <th
                                        class="border border-white/20 px-3 py-3 text-center font-black"
                                    >
                                        Tanggal
                                    </th>

                                    <th
                                        class="border border-white/20 px-3 py-3 text-center font-black"
                                    >
                                        Periode
                                    </th>

                                    <th
                                        class="border border-white/20 px-3 py-3 text-center font-black"
                                    >
                                        No.
                                    </th>

                                    <th
                                        class="border border-white/20 px-3 py-3 text-left font-black"
                                    >
                                        Nama Anggota
                                    </th>

                                    <th
                                        class="border border-white/20 px-3 py-3 text-center font-black"
                                    >
                                        Jenis
                                    </th>

                                    <th
                                        class="border border-white/20 px-3 py-3 text-left font-black"
                                    >
                                        Rincian
                                    </th>

                                    <th
                                        class="border border-white/20 px-3 py-3 text-right font-black"
                                    >
                                        Nominal
                                    </th>

                                    <th
                                        class="border border-white/20 px-3 py-3 text-right font-black"
                                    >
                                        Jasa
                                    </th>

                                    <th
                                        class="border border-white/20 px-3 py-3 text-right font-black"
                                    >
                                        Sisa Pinjaman
                                    </th>

                                    <th
                                        class="border border-white/20 px-3 py-3 text-left font-black"
                                    >
                                        Keterangan
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="history in paginatedHistories"
                                    :key="history.key"
                                    class="transition hover:bg-blue-50/60"
                                >
                                    <td
                                        class="whitespace-nowrap border border-blue-100 px-3 py-2 text-center text-slate-600"
                                    >
                                        {{ formatDate(history.tanggal) }}
                                    </td>

                                    <td
                                        class="whitespace-nowrap border border-blue-100 px-3 py-2 text-center font-semibold text-slate-500"
                                    >
                                        {{ history.periode }}
                                    </td>

                                    <td
                                        class="whitespace-nowrap border border-blue-100 px-3 py-2 text-center font-bold text-[#1a6fbd]"
                                    >
                                        {{ history.nomor_anggota }}
                                    </td>

                                    <td
                                        class="whitespace-nowrap border border-blue-100 px-3 py-2 font-bold text-slate-700"
                                    >
                                        {{ history.nama }}
                                    </td>

                                    <td
                                        class="whitespace-nowrap border border-blue-100 px-3 py-2 text-center"
                                    >
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-black ring-1"
                                            :class="badgeClass(history.jenis)"
                                        >
                                            {{ history.jenis_label }}
                                        </span>
                                    </td>

                                    <td
                                        class="whitespace-nowrap border border-blue-100 px-3 py-2 text-slate-600"
                                    >
                                        {{ history.rincian || "-" }}
                                    </td>

                                    <td
                                        class="whitespace-nowrap border border-blue-100 px-3 py-2 text-right font-bold tabular-nums text-slate-700"
                                    >
                                        {{ formatNumber(history.nominal) }}
                                    </td>

                                    <td
                                        class="whitespace-nowrap border border-blue-100 px-3 py-2 text-right font-semibold tabular-nums text-orange-600"
                                    >
                                        {{ formatNumber(history.jasa) }}
                                    </td>

                                    <td
                                        class="whitespace-nowrap border border-blue-100 px-3 py-2 text-right font-semibold tabular-nums text-slate-600"
                                    >
                                        {{
                                            formatNumber(history.sisa_pinjaman)
                                        }}
                                    </td>

                                    <td
                                        class="border border-blue-100 px-3 py-2 text-slate-500"
                                    >
                                        {{ history.keterangan || "-" }}
                                    </td>
                                </tr>

                                <tr v-if="paginatedHistories.length === 0">
                                    <td
                                        colspan="10"
                                        class="px-4 py-16 text-center text-sm font-bold text-slate-400"
                                    >
                                        Data transaksi tidak ditemukan
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <Pagination :pagination="pagination" @change="changePage" />
                </section>
            </Reveal>
        </template>
    </AuthenticatedLayout>
</template>
