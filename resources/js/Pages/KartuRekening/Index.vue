<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import AnggotaFormModal from "@/Components/KartuRekening/AnggotaFormModal.vue";
import KartuRekeningSheet from "@/Components/KartuRekening/KartuRekeningSheet.vue";
import ConfirmModal from "@/Components/UI/ConfirmModal.vue";
import ToastAlert from "@/Components/UI/ToastAlert.vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import { computed, nextTick, ref, watch } from "vue";
const props = defineProps({
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

const page = usePage();

const clone = (value) => {
    return JSON.parse(JSON.stringify(value));
};

const flash = computed(() => {
    return page.props.flash ?? {};
});

const errors = computed(() => {
    return page.props.errors ?? {};
});

const toast = ref(null);

const localMembers = ref(clone(props.members));

const originalMembers = ref(clone(props.members));

const dirtyChanges = ref({});

const search = ref("");

const selectedYear = ref(props.filters.tahun);

const showAnggotaModal = ref(false);

const showDeleteModal = ref(false);

const showKeluarkanModal = ref(false);

const selectedMember = ref(null);

const saveProcessing = ref(false);

const anggotaForm = useForm({
    nama: "",
    tanggal_masuk: "",
});

const dirtyCount = computed(() => {
    return Object.keys(dirtyChanges.value).length;
});

const hasDirty = computed(() => {
    return dirtyCount.value > 0;
});

const filteredMembers = computed(() => {
    const keyword = search.value.trim().toLowerCase();

    if (!keyword) {
        return localMembers.value;
    }

    return localMembers.value.filter((member) => {
        return (
            member.nama.toLowerCase().includes(keyword) ||
            member.nomor_anggota.toLowerCase().includes(keyword)
        );
    });
});

watch(
    () => props.members,
    (members) => {
        localMembers.value = clone(members);

        originalMembers.value = clone(members);

        dirtyChanges.value = {};
    },
    {
        deep: true,
    },
);

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

const isLoanSection = (section) => {
    return ["reguler", "sebrak"].includes(section);
};

const makeKey = (change) => {
    const keys = [
        change.anggota_id,

        change.periode,

        change.section,

        change.field,
    ];

    if (isLoanSection(change.section)) {
        keys.push(change.client_key ?? `${change.action}-${change.entry_id}`);
    }

    return keys.join("|");
};

const findMember = (list, anggotaId) => {
    return list.find((member) => Number(member.id) === Number(anggotaId));
};

const findRow = (member, periode) => {
    return member.rows.find((row) => row.periode === periode);
};

const findLoanEntry = (row, change) => {
    return row[change.section].entries.find((entry) => {
        if (change.client_key) {
            return entry.client_key === change.client_key;
        }

        return Number(entry.entry_id) === Number(change.entry_id);
    });
};

const getOriginalValue = (change) => {
    const member = findMember(originalMembers.value, change.anggota_id);

    if (!member) {
        return undefined;
    }

    if (change.section === "anggota") {
        return member.nama;
    }

    const row = findRow(member, change.periode);

    if (!row) {
        return undefined;
    }

    if (change.section === "simpanan") {
        return row.simpanan[change.field];
    }

    const entry = findLoanEntry(row, change);

    return entry?.jumlah;
};

const setLocalValue = (change) => {
    const member = findMember(localMembers.value, change.anggota_id);

    if (!member) {
        return;
    }

    if (change.section === "anggota") {
        member.nama = change.value;

        return;
    }

    const row = findRow(member, change.periode);

    if (!row) {
        return;
    }

    if (change.section === "simpanan") {
        row.simpanan[change.field] = change.value;

        hitungPreviewRow(row);

        return;
    }

    const entry = findLoanEntry(row, change);

    if (entry) {
        entry.jumlah = change.value;
    }

    hitungPreviewRow(row);
};

const hitungPreviewRow = (row) => {
    row.simpanan.jumlah_simpanan = [
        "simpanan_pokok",
        "simpanan_wajib",
        "simpanan_sukarela",
        "simpanan_hari_raya",
        "simpanan_rekreasi",
    ].reduce((total, field) => {
        return total + Number(row.simpanan[field] || 0);
    }, 0);

    const totalReguler = totalTagihanPinjamanPreview(row.reguler);

    const totalSebrak = totalTagihanPinjamanPreview(row.sebrak);

    row.jumlah_tagihan =
        row.simpanan.jumlah_simpanan + totalReguler + totalSebrak;
};

const totalTagihanPinjamanPreview = (loan) => {
    const angsuran = loan.entries.find(
        (entry) => entry.entry_type === "angsuran",
    );

    return Number(angsuran?.jumlah || 0) + Number(loan.jasa || 0);
};

const normalize = (section, value) => {
    if (section === "anggota") {
        return String(value ?? "").trim();
    }

    if (value === "" || value === null || value === undefined) {
        return "";
    }

    return Number(value);
};

const handleChange = (change) => {
    const key = makeKey(change);

    const originalValue = getOriginalValue(change);

    setLocalValue(change);

    if (
        normalize(change.section, originalValue) ===
        normalize(change.section, change.value)
    ) {
        delete dirtyChanges.value[key];

        return;
    }

    dirtyChanges.value[key] = change;
};

const discardNewLoanEntry = (clientKey) => {
    Object.keys(dirtyChanges.value)
        .filter((key) => key.endsWith(`|${clientKey}`))
        .forEach((key) => {
            delete dirtyChanges.value[key];
        });
};

const saveChanges = () => {
    if (!hasDirty.value) {
        return;
    }

    saveProcessing.value = true;

    router.patch(
        route("kartu-rekening.update"),
        {
            tahun: selectedYear.value,

            changes: Object.values(dirtyChanges.value),
        },
        {
            preserveScroll: true,

            onFinish: () => {
                saveProcessing.value = false;
            },
        },
    );
};

const changeYear = () => {
    if (
        hasDirty.value &&
        !window.confirm("Perubahan belum disimpan. Tetap ganti tahun?")
    ) {
        selectedYear.value = props.filters.tahun;

        return;
    }

    router.get(
        route("kartu-rekening.index"),
        {
            tahun: selectedYear.value,
        },
        {
            preserveState: false,

            replace: true,
        },
    );
};

const submitAnggota = () => {
    anggotaForm.post(route("anggota.store"), {
        preserveScroll: true,

        onSuccess: () => {
            showAnggotaModal.value = false;

            anggotaForm.reset();
        },
    });
};

const openDeleteModal = (member) => {
    selectedMember.value = member;

    showDeleteModal.value = true;
};

const openKeluarkanModal = (member) => {
    selectedMember.value = member;

    showKeluarkanModal.value = true;
};

const submitDelete = () => {
    if (!selectedMember.value) {
        return;
    }

    router.delete(route("anggota.destroy", selectedMember.value.id), {
        preserveScroll: true,

        onSuccess: () => {
            showDeleteModal.value = false;

            selectedMember.value = null;
        },
    });
};

const submitKeluarkan = () => {
    if (!selectedMember.value) {
        return;
    }

    router.patch(
        route("anggota.keluarkan", selectedMember.value.id),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                showKeluarkanModal.value = false;

                selectedMember.value = null;
            },
        },
    );
};

const printAll = async () => {
    await nextTick();

    window.print();
};

const closeToast = () => {
    toast.value = null;
};
</script>

<template>
    <Head title="Kartu Rekening — DigiSejahtera" />

    <AuthenticatedLayout>
        <template #title> Kartu Rekening </template>

        <ToastAlert
            v-if="toast"
            :key="toast.id"
            :message="toast.message"
            :type="toast.type"
            @close="closeToast"
        />

        <div class="screen-only">
            <section
                class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0f4f8e] via-[#1a6fbd] to-[#3aab2e] px-6 py-7 text-white shadow-xl shadow-blue-200/70 sm:px-8"
            >
                <p
                    class="text-xs font-bold uppercase tracking-[0.2em] text-blue-100"
                >
                    Administrasi Koperasi
                </p>

                <h2 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">
                    Kartu Rekening Anggota
                </h2>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-50/90">
                    Isi transaksi secara langsung seperti pencatatan Excel.
                    Gunakan menu + Input pada kolom jumlah pinjaman untuk
                    mencatat angsuran maupun pinjaman tambahan.
                </p>
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
                            v-model="selectedYear"
                            class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-bold text-[#1a6fbd] outline-none"
                            @change="changeYear"
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

                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="rounded-xl bg-gradient-to-r from-[#1a6fbd] to-[#0f4f8e] px-4 py-3 text-sm font-bold text-white shadow-md shadow-blue-200 transition hover:-translate-y-0.5"
                            @click="showAnggotaModal = true"
                        >
                            + Tambah Anggota
                        </button>

                        <a
                            :href="
                                route('kartu-rekening.export', {
                                    tahun: filters.tahun,
                                })
                            "
                            class="inline-flex items-center gap-2 rounded-xl bg-[#1a6fbd] px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-100 transition hover:-translate-y-0.5 hover:bg-[#155d9e]"
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

                            Cetak Kartu Rekening
                        </a>
                    </div>
                </div>

                <p class="mt-3 text-xs leading-5 text-slate-400">
                    Klik sel simpanan untuk mengisi setoran atau penarikan.
                    Gunakan nilai negatif untuk penarikan. Pada kolom jumlah
                    pinjaman, pilih Tambah Angsuran atau Tambah Pinjaman dari
                    menu yang tersedia.
                </p>
            </section>

            <section class="mt-5 space-y-5">
                <KartuRekeningSheet
                    v-for="member in filteredMembers"
                    :key="member.id"
                    :member="member"
                    :dirty-keys="dirtyChanges"
                    @change="handleChange"
                    @discard="discardNewLoanEntry"
                    @delete="openDeleteModal"
                    @keluarkan="openKeluarkanModal"
                />

                <div
                    v-if="filteredMembers.length === 0"
                    class="rounded-3xl border border-blue-100 bg-white py-16 text-center shadow-[0_8px_28px_rgba(26,111,189,0.08)]"
                >
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-2xl"
                    >
                        👥
                    </div>

                    <p class="mt-4 text-sm font-bold text-slate-600">
                        Data anggota tidak ditemukan
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Periksa kata kunci pencarian atau tambahkan anggota
                        baru.
                    </p>
                </div>
            </section>

            <Transition
                enter-active-class="transition duration-300 ease-out"
                leave-active-class="transition duration-200 ease-in"
                enter-from-class="translate-y-5 opacity-0"
                leave-to-class="translate-y-5 opacity-0"
            >
                <div v-if="hasDirty" class="fixed bottom-6 right-6 z-40">
                    <button
                        type="button"
                        :disabled="saveProcessing"
                        class="flex items-center gap-3 rounded-2xl bg-gradient-to-r from-[#f07c1a] to-[#f9a54a] px-5 py-4 text-sm font-black text-white shadow-xl shadow-orange-200 transition hover:-translate-y-1 disabled:cursor-not-allowed disabled:opacity-60"
                        @click="saveChanges"
                    >
                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-white/20"
                        >
                            ✓
                        </span>

                        {{
                            saveProcessing
                                ? "Menyimpan..."
                                : `Simpan ${dirtyCount} Perubahan`
                        }}
                    </button>
                </div>
            </Transition>
        </div>

        <div class="print-only">
            <KartuRekeningSheet
                v-for="member in localMembers"
                :key="`print-${member.id}`"
                :member="member"
                :dirty-keys="{}"
                print-mode
            />
        </div>

        <AnggotaFormModal
            :show="showAnggotaModal"
            :form="anggotaForm"
            @close="showAnggotaModal = false"
            @submit="submitAnggota"
        />

        <ConfirmModal
            :show="showDeleteModal"
            title="Hapus Anggota?"
            :message="`Data anggota ${selectedMember?.nama ?? ''} akan dihapus permanen dari database.`"
            confirm-text="Hapus"
            variant="danger"
            @close="showDeleteModal = false"
            @confirm="submitDelete"
        />

        <ConfirmModal
            :show="showKeluarkanModal"
            title="Keluarkan Anggota?"
            :message="`Status anggota ${selectedMember?.nama ?? ''} akan diubah menjadi nonaktif. Riwayat transaksi tetap tersimpan.`"
            confirm-text="Keluarkan"
            variant="warning"
            @close="showKeluarkanModal = false"
            @confirm="submitKeluarkan"
        />
    </AuthenticatedLayout>
</template>

<style>
.print-only {
    display: none;
}

@media print {
    body {
        background: white !important;
    }

    .screen-only,
    .no-print,
    aside,
    header.sticky {
        display: none !important;
    }

    .print-only {
        display: block !important;
    }

    .lg\:pl-72 {
        padding-left: 0 !important;
    }

    main {
        padding: 0 !important;
    }

    .print-sheet {
        break-after: page;
        border-radius: 0 !important;
        box-shadow: none !important;
        margin-bottom: 0 !important;
    }

    .print-sheet input {
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }
}
</style>
