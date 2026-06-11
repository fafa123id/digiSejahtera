<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import KitirCard from "@/Components/Kitir/KitirCard.vue";
import { Head, router } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import Reveal from "@/Components/UI/Reveal.vue";
import ToastAlert from "@/Components/UI/ToastAlert.vue";

const props = defineProps({
    kitirs: {
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

    months: {
        type: Array,
        default: () => [],
    },
});

const search = ref("");

const selectedYear = ref(props.filters.tahun);

const selectedMonth = ref(props.filters.bulan);

const filteredKitirs = computed(() => {
    const keyword = search.value.trim().toLowerCase();

    if (!keyword) {
        return props.kitirs;
    }

    return props.kitirs.filter((kitir) => {
        return (
            kitir.nama.toLowerCase().includes(keyword) ||
            String(kitir.nomor_anggota).includes(keyword)
        );
    });
});

const selectedMonthLabel = computed(() => {
    return (
        props.months.find(
            (month) => Number(month.value) === Number(selectedMonth.value),
        )?.label ?? ""
    );
});

const changePeriod = () => {
    router.get(
        route("kitir.index"),
        {
            tahun: selectedYear.value,

            bulan: selectedMonth.value,
        },
        {
            preserveState: false,

            replace: true,
        },
    );
};
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
    <Head title="KITIR — DigiSejahtera" />

    <AuthenticatedLayout>
        <template #title> KITIR </template>
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
                        KITIR Anggota
                    </h2>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-50/90">
                        Tampilkan rincian potongan bulanan seluruh anggota
                        koperasi berdasarkan periode yang dipilih.
                    </p>
                </div>
            </section>

            <section
                class="mt-6 rounded-3xl border border-blue-100 bg-white p-4 shadow-[0_8px_28px_rgba(26,111,189,0.08)]"
            >
                <div
                    class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Cari nama atau nomor anggota..."
                            class="w-full rounded-xl border border-blue-100 bg-[#f8fbff] px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#1a6fbd] focus:bg-white focus:ring-4 focus:ring-blue-100 sm:w-80"
                        />

                        <select
                            v-model="selectedMonth"
                            class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-bold text-[#1a6fbd] outline-none"
                            @change="changePeriod"
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
                            class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-bold text-[#1a6fbd] outline-none"
                            @change="changePeriod"
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

                    <div class="flex flex-wrap items-center gap-2">
                        <div
                            class="rounded-xl bg-green-50 px-4 py-3 text-xs font-bold text-green-700"
                        >
                            {{ filteredKitirs.length }} Anggota
                        </div>

                        <a
                            :href="
                                route('kitir.export', {
                                    tahun: selectedYear,

                                    bulan: selectedMonth,
                                })
                            "
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#1a6fbd] to-[#0f4f8e] px-4 py-3 text-sm font-bold text-white shadow-md shadow-blue-200 transition hover:-translate-y-0.5"
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

                            Cetak KITIR
                        </a>
                    </div>
                </div>

                <p class="mt-3 text-xs leading-5 text-slate-400">
                    Periode {{ selectedMonthLabel }} {{ selectedYear }}. KITIR
                    hanya menampilkan transaksi yang tercatat pada periode
                    tersebut dan saldo pinjaman anggota sampai akhir periode.
                </p>
            </section>

            <section class="mt-5 grid gap-5 xl:grid-cols-3">
                <KitirCard
                    v-for="kitir in filteredKitirs"
                    :key="kitir.id"
                    :kitir="kitir"
                />

                <div
                    v-if="filteredKitirs.length === 0"
                    class="col-span-full rounded-3xl border border-blue-100 bg-white py-16 text-center shadow-[0_8px_28px_rgba(26,111,189,0.08)]"
                >
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-2xl"
                    >
                        🧾
                    </div>

                    <p class="mt-4 text-sm font-bold text-slate-600">
                        Data KITIR tidak ditemukan
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Periksa periode atau kata kunci pencarian.
                    </p>
                </div>
            </section>
        </Reveal>
    </AuthenticatedLayout>
</template>
