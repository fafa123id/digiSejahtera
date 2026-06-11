<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/UI/Pagination.vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import Reveal from "@/Components/UI/Reveal.vue";
import ToastAlert from "@/Components/UI/ToastAlert.vue";

const props = defineProps({
    report: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        required: true,
    },

    types: {
        type: Array,
        default: () => [],
    },

    years: {
        type: Array,
        default: () => [],
    },
});

const perPage = 10;

const search = ref("");

const selectedType = ref(props.filters.jenis);

const selectedYear = ref(props.filters.tahun);

const currentPage = ref(1);

const filteredRows = computed(() => {
    const keyword = search.value.trim().toLowerCase();

    if (!keyword) {
        return props.report.rows;
    }

    return props.report.rows.filter((row) => {
        const nomorAnggota = String(row.nomor_anggota ?? "").toLowerCase();
        const nama = String(row.nama ?? "").toLowerCase();

        return nomorAnggota.includes(keyword) || nama.includes(keyword);
    });
});

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(filteredRows.value.length / perPage));
});

const paginatedRows = computed(() => {
    const start = (currentPage.value - 1) * perPage;

    return filteredRows.value.slice(start, start + perPage);
});

const pagination = computed(() => {
    const total = filteredRows.value.length;
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

watch(search, () => {
    currentPage.value = 1;
});

watch(totalPages, (pages) => {
    if (currentPage.value > pages) {
        currentPage.value = pages;
    }
});

const changePage = (page) => {
    if (page < 1 || page > totalPages.value || page === currentPage.value) {
        return;
    }

    currentPage.value = page;
};

const reloadReport = () => {
    router.get(
        route("laporan.index"),
        {
            jenis: selectedType.value,
            tahun: selectedYear.value,
        },
        {
            preserveState: false,
            replace: true,
        },
    );
};

const formatNumber = (value) => {
    const number = Number(value ?? 0);

    if (number === 0) {
        return "-";
    }

    return new Intl.NumberFormat("id-ID", {
        maximumFractionDigits: 0,
    }).format(number);
};

const alignmentClass = (column) => {
    if (column.align === "center") {
        return "text-center";
    }

    if (column.align === "right") {
        return "text-right";
    }

    return "text-left";
};
const page = usePage();

const errors = computed(() => {
    return page.props.errors ?? {};
});

const flash = computed(() => {
    return page.props.flash ?? {};
});
const toast = ref(null);
watch(
    () => flash.value.toast,
    (newToast) => {
        if (newToast) {
            toast.value = newToast;
        }
    },
    {
        immediate: true,
        deep: true,
    },
);

watch(
    errors,
    (newErrors) => {
        const firstError = Object.values(newErrors)[0];

        if (!firstError) {
            return;
        }

        toast.value = {
            id: `error-${Date.now()}`,
            type: "error",
            message: firstError,
        };
    },
    {
        deep: true,
    },
);
const closeToast = () => {
    toast.value = null;
};
</script>

<template>
    <Head title="Laporan — DigiSejahtera" />

    <AuthenticatedLayout>
        <template #title> Laporan </template>
        <Reveal direction="down" :duration="700">
            <ToastAlert
                v-if="toast"
                :key="toast.id"
                :message="toast.message"
                :type="toast.type"
                @close="closeToast"
            />
            <!-- Heading -->
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
                        {{ report.title }}
                    </h2>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-50/90">
                        {{ report.description }}
                    </p>
                </div>
            </section>

            <section
                class="mt-6 rounded-3xl border border-blue-100 bg-white p-4 shadow-[0_8px_28px_rgba(26,111,189,0.08)]"
            >
                <div
                    class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between"
                >
                    <div class="flex flex-col gap-3 md:flex-row">
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Cari nama atau nomor anggota..."
                            class="w-full rounded-xl border border-blue-100 bg-[#f8fbff] px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#1a6fbd] focus:bg-white focus:ring-4 focus:ring-blue-100 sm:w-80"
                        />

                        <select
                            v-model="selectedType"
                            class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-bold text-[#1a6fbd] outline-none"
                            @change="reloadReport"
                        >
                            <option
                                v-for="type in types"
                                :key="type.value"
                                :value="type.value"
                            >
                                {{ type.label }}
                            </option>
                        </select>

                        <select
                            v-model="selectedYear"
                            class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-bold text-[#1a6fbd] outline-none"
                            @change="reloadReport"
                        >
                            <option
                                v-for="year in years"
                                :key="year"
                                :value="year"
                            >
                                {{ year }}
                            </option>
                        </select>
                    </div>

                    <a
                        :href="report.export_url"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#3aab2e] to-[#24851c] px-5 py-3 text-sm font-bold text-white shadow-md shadow-green-200 transition hover:-translate-y-0.5"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 4v11m0 0 4-4m-4 4-4-4m-4 7h16"
                            />
                        </svg>

                        Cetak Excel
                    </a>
                </div>
            </section>

            <section
                class="mt-5 overflow-hidden rounded-3xl border border-blue-100 bg-white shadow-[0_8px_28px_rgba(26,111,189,0.08)]"
            >
                <div class="overflow-x-auto">
                    <table class="w-full min-w-max border-collapse text-xs">
                        <thead>
                            <tr
                                class="bg-gradient-to-r from-[#0f4f8e] via-[#1a6fbd] to-[#3aab2e] text-white"
                            >
                                <th
                                    v-for="column in report.columns"
                                    :key="column.key"
                                    class="whitespace-nowrap border border-white/20 px-3 py-3 font-black"
                                    :class="alignmentClass(column)"
                                >
                                    {{ column.label }}
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="row in paginatedRows"
                                :key="row.id"
                                class="transition hover:bg-blue-50/60"
                            >
                                <td
                                    v-for="column in report.columns"
                                    :key="column.key"
                                    class="whitespace-nowrap border border-blue-100 px-3 py-2 tabular-nums text-slate-700"
                                    :class="alignmentClass(column)"
                                >
                                    {{
                                        column.money
                                            ? formatNumber(row[column.key])
                                            : row[column.key]
                                    }}
                                </td>
                            </tr>

                            <tr v-if="paginatedRows.length === 0">
                                <td
                                    :colspan="report.columns.length"
                                    class="px-4 py-16 text-center text-sm font-bold text-slate-400"
                                >
                                    Data laporan tidak ditemukan.
                                </td>
                            </tr>
                        </tbody>

                        <tfoot v-if="filteredRows.length > 0">
                            <tr class="bg-slate-50 font-black text-slate-700">
                                <td
                                    v-for="column in report.columns"
                                    :key="column.key"
                                    class="whitespace-nowrap border border-blue-100 px-3 py-3 tabular-nums"
                                    :class="alignmentClass(column)"
                                >
                                    <span v-if="column.key === 'nama'">
                                        JUMLAH
                                    </span>

                                    <span v-else-if="column.money">
                                        {{
                                            formatNumber(
                                                report.totals[column.key],
                                            )
                                        }}
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <Pagination :pagination="pagination" @change="changePage" />
            </section>
        </Reveal>
    </AuthenticatedLayout>
</template>
