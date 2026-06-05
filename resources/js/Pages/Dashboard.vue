<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ChartPanel from "@/Components/Dashboard/ChartPanel.vue";
import RecentTransactionList from "@/Components/Dashboard/RecentTransactionList.vue";
import StatCard from "@/Components/Dashboard/StatCard.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

import {
    ArcElement,
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Tooltip,
} from "chart.js";

import { Doughnut, Line } from "vue-chartjs";

ChartJS.register(
    ArcElement,
    CategoryScale,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Tooltip,
);

const props = defineProps({
    summary: {
        type: Object,
        required: true,
    },

    monthlyTrend: {
        type: Array,
        default: () => [],
    },

    loanComposition: {
        type: Object,
        required: true,
    },

    recentTransactions: {
        type: Array,
        default: () => [],
    },

    generatedAt: {
        type: String,
        default: "",
    },
});

const page = usePage();

const user = computed(() => {
    return page.props.auth.user;
});

const trendChartData = computed(() => {
    return {
        labels: props.monthlyTrend.map((item) => item.month),

        datasets: [
            {
                label: "Simpanan",
                data: props.monthlyTrend.map((item) => item.savings),
                borderColor: "#3aab2e",
                backgroundColor: "rgba(58, 171, 46, 0.10)",
                pointBackgroundColor: "#3aab2e",
                pointBorderColor: "#ffffff",
                pointBorderWidth: 3,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.38,
                fill: true,
            },

            {
                label: "Pinjaman Baru",
                data: props.monthlyTrend.map((item) => item.loans),
                borderColor: "#1a6fbd",
                backgroundColor: "rgba(26, 111, 189, 0.08)",
                pointBackgroundColor: "#1a6fbd",
                pointBorderColor: "#ffffff",
                pointBorderWidth: 3,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.38,
                fill: true,
            },

            {
                label: "Angsuran Pokok",
                data: props.monthlyTrend.map((item) => item.installments),
                borderColor: "#f07c1a",
                backgroundColor: "rgba(240, 124, 26, 0.06)",
                pointBackgroundColor: "#f07c1a",
                pointBorderColor: "#ffffff",
                pointBorderWidth: 3,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.38,
                fill: true,
            },
        ],
    };
});

const trendChartOptions = {
    responsive: true,
    maintainAspectRatio: false,

    animation: {
        duration: 1100,
        easing: "easeOutQuart",
    },

    interaction: {
        intersect: false,
        mode: "index",
    },

    plugins: {
        legend: {
            position: "bottom",

            labels: {
                usePointStyle: true,
                boxWidth: 8,
                padding: 18,
            },
        },

        tooltip: {
            callbacks: {
                label(context) {
                    return `${context.dataset.label}: ${formatCurrency(context.raw)}`;
                },
            },
        },
    },

    scales: {
        x: {
            grid: {
                display: false,
            },

            ticks: {
                color: "#94a3b8",
                font: {
                    size: 11,
                },
            },
        },

        y: {
            beginAtZero: true,

            grid: {
                color: "#eef2f9",
            },

            ticks: {
                color: "#94a3b8",

                callback(value) {
                    return formatCompactCurrency(value);
                },
            },
        },
    },
};

const hasLoanComposition = computed(() => {
    return props.loanComposition.reguler + props.loanComposition.sebrak > 0;
});

const loanChartData = computed(() => {
    return {
        labels: ["Reguler", "Sebrak"],

        datasets: [
            {
                data: [
                    props.loanComposition.reguler,
                    props.loanComposition.sebrak,
                ],

                backgroundColor: ["#1a6fbd", "#3aab2e"],

                borderWidth: 0,
                hoverOffset: 8,
            },
        ],
    };
});

const loanChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: "68%",

    animation: {
        animateRotate: true,
        duration: 1100,
        easing: "easeOutQuart",
    },

    plugins: {
        legend: {
            position: "bottom",

            labels: {
                usePointStyle: true,
                boxWidth: 8,
                padding: 18,
            },
        },

        tooltip: {
            callbacks: {
                label(context) {
                    return `${context.label}: ${formatCurrency(context.raw)}`;
                },
            },
        },
    },
};

function formatCurrency(value) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));
}

function formatCompactCurrency(value) {
    const nominal = Number(value ?? 0);

    if (Math.abs(nominal) >= 1_000_000_000) {
        return `Rp${(nominal / 1_000_000_000).toFixed(1)}M`;
    }

    if (Math.abs(nominal) >= 1_000_000) {
        return `Rp${(nominal / 1_000_000).toFixed(1)}jt`;
    }

    if (Math.abs(nominal) >= 1_000) {
        return `Rp${(nominal / 1_000).toFixed(0)}rb`;
    }

    return `Rp${nominal}`;
}
</script>

<template>
    <Head title="Dashboard — DigiSejahtera" />

    <AuthenticatedLayout>
        <template #title> Dashboard </template>

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
                <span class="h-2 w-2 animate-pulse rounded-sm bg-white/40" />
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
                    Ringkasan Koperasi
                </p>

                <h2 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">
                    Selamat datang,
                    {{ user?.name }}
                </h2>

                <p class="mt-2 max-w-xl text-sm leading-6 text-blue-50/90">
                    Pantau perkembangan keuangan Koperasi Sejahtera melalui
                    ringkasan informasi dan grafik transaksi terbaru.
                </p>

                <p class="mt-4 text-xs font-semibold text-blue-100/80">
                    Diperbarui:
                    {{ generatedAt }}
                </p>
            </div>
        </section>

        <!-- Statistik -->
        <section class="mt-6">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard
                    title="Total Simpanan"
                    :value="summary.total_savings"
                    format="currency"
                    description="Saldo seluruh anggota"
                    variant="blue"
                />

                <StatCard
                    title="Pinjaman Aktif"
                    :value="summary.active_loans"
                    format="currency"
                    :description="`${summary.active_loan_members ?? 0} anggota`"
                    variant="green"
                />

                <StatCard
                    title="SHU Periode Ini"
                    :value="summary.current_shu ?? 0"
                    format="currency"
                    :description="summary.current_period ?? '-'"
                    variant="orange"
                />

                <StatCard
                    title="Total Anggota"
                    :value="summary.active_members"
                    :description="`${summary.active_members} aktif`"
                    variant="teal"
                />
            </div>
        </section>
        <section
            class="mt-5 rounded-2xl border border-blue-100 bg-white p-5 shadow-[0_8px_28px_rgba(26,111,189,0.08)]"
        >
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium text-blue-700/70">
                        Angsuran Bulan Ini
                    </p>

                    <p class="mt-1 text-lg font-black text-slate-900">
                        {{ formatCurrency(summary.monthly_installments) }}
                    </p>
                </div>

                <span
                    class="rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-600"
                >
                    Periode berjalan
                </span>
            </div>
        </section>
        <!-- Grafik -->
        <section
            class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.8fr)_minmax(300px,1fr)]"
        >
            <ChartPanel
                title="Tren Transaksi Bulanan"
                description="Perbandingan simpanan, pinjaman baru, dan angsuran pokok selama 12 bulan terakhir."
            >
                <div class="h-[330px]">
                    <Line :data="trendChartData" :options="trendChartOptions" />
                </div>
            </ChartPanel>

            <ChartPanel
                title="Komposisi Pinjaman Aktif"
                description="Perbandingan sisa pinjaman reguler dan pinjaman sebrak."
            >
                <div v-if="hasLoanComposition" class="h-[330px]">
                    <Doughnut
                        :data="loanChartData"
                        :options="loanChartOptions"
                    />
                </div>

                <div
                    v-else
                    class="flex h-[330px] flex-col items-center justify-center text-center"
                >
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-2xl"
                    >
                        🏦
                    </div>

                    <p class="mt-4 text-sm font-bold text-slate-600">
                        Belum ada pinjaman aktif
                    </p>

                    <p
                        class="mt-1 max-w-[220px] text-xs leading-5 text-slate-400"
                    >
                        Grafik komposisi akan muncul setelah data pinjaman
                        dicatat.
                    </p>
                </div>
            </ChartPanel>
        </section>

        <!-- Aktivitas -->
        <section
            class="mt-6 rounded-3xl border border-blue-100 bg-white p-5 shadow-[0_8px_28px_rgba(26,111,189,0.08)]"
        >
            <div>
                <h3 class="text-base font-black text-slate-800">
                    Transaksi Terbaru
                </h3>

                <p class="mt-1 text-xs leading-5 text-slate-400">
                    Aktivitas simpanan, pinjaman, dan pembayaran angsuran
                    terbaru.
                </p>
            </div>

            <div class="mt-4">
                <RecentTransactionList :transactions="recentTransactions" />
            </div>
        </section>
    </AuthenticatedLayout>
</template>
