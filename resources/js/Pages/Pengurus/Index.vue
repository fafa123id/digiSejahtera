<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ConfirmModal from "@/Components/UI/ConfirmModal.vue";
import Pagination from "@/Components/UI/Pagination.vue";
import ToastAlert from "@/Components/UI/ToastAlert.vue";
import PengurusFormModal from "@/Components/Pengurus/PengurusFormModal.vue";
import TemporaryPasswordModal from "@/Components/Pengurus/TemporaryPasswordModal.vue";
import Reveal from "@/Components/UI/Reveal.vue";

import { Head, router, useForm, usePage } from "@inertiajs/vue3";

import { computed, ref, watch } from "vue";

const props = defineProps({
    pengurus: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();

const flash = computed(() => {
    return page.props.flash ?? {};
});

const toast = ref(null);
const pendingToast = ref(null);

const search = ref(props.filters.search ?? "");

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showPasswordModal = ref(false);
const selectedPengurus = ref(null);
const temporaryPassword = ref(null);
const deleteProcessing = ref(false);

const createForm = useForm({
    name: "",
    username: "",
});

const editForm = useForm({
    name: "",
    username: "",
});

watch(
    () => flash.value.toast,
    (newToast) => {
        if (!newToast) {
            return;
        }
        if (flash.value.temporary_password) {
            pendingToast.value = newToast;

            return;
        }

        toast.value = newToast;
    },
    {
        immediate: true,
        deep: true,
    },
);

watch(
    () => flash.value.temporary_password,
    (credential) => {
        if (!credential) {
            return;
        }

        temporaryPassword.value = credential;
        showPasswordModal.value = true;
    },
    {
        immediate: true,
        deep: true,
    },
);

const closeToast = () => {
    toast.value = null;
};

const submitSearch = () => {
    router.get(
        route("pengurus.index"),
        {
            search: search.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const resetSearch = () => {
    search.value = "";

    submitSearch();
};

const openCreateModal = () => {
    createForm.reset();
    createForm.clearErrors();

    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;

    createForm.clearErrors();
};

const submitCreate = () => {
    createForm.post(route("pengurus.store"), {
        preserveScroll: true,

        onSuccess: () => {
            closeCreateModal();
            createForm.reset();
        },
    });
};

const openEditModal = (pengurus) => {
    selectedPengurus.value = pengurus;

    editForm.clearErrors();

    editForm.name = pengurus.name;
    editForm.username = pengurus.username;

    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedPengurus.value = null;

    editForm.clearErrors();
};

const submitEdit = () => {
    if (!selectedPengurus.value) {
        return;
    }

    editForm.put(route("pengurus.update", selectedPengurus.value.id), {
        preserveScroll: true,

        onSuccess: () => {
            closeEditModal();
        },
    });
};

const openDeleteModal = (pengurus) => {
    selectedPengurus.value = pengurus;

    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    selectedPengurus.value = null;
};

const submitDelete = () => {
    if (!selectedPengurus.value) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(route("pengurus.destroy", selectedPengurus.value.id), {
        preserveScroll: true,

        onSuccess: () => {
            closeDeleteModal();
        },

        onFinish: () => {
            deleteProcessing.value = false;
        },
    });
};

const resetPassword = (pengurus) => {
    router.patch(
        route("pengurus.reset-password", pengurus.id),
        {},
        {
            preserveScroll: true,
        },
    );
};

const closePasswordModal = () => {
    temporaryPassword.value = null;
    showPasswordModal.value = false;

    if (pendingToast.value) {
        toast.value = pendingToast.value;
        pendingToast.value = null;
    }
};
</script>

<template>
    <Head title="Data Pengurus — DigiSejahtera" />

    <AuthenticatedLayout>
        <template #title> Data Pengurus </template>

        <!-- Toast notification -->
        <ToastAlert
            v-if="toast"
            :key="toast.id"
            :message="toast.message"
            :type="toast.type"
            @close="closeToast"
        />
        <Reveal direction="down" :duration="700">
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

                <div class="relative">
                    <p
                        class="text-xs font-bold uppercase tracking-[0.2em] text-blue-100"
                    >
                        Administrasi Sistem
                    </p>

                    <h2
                        class="mt-2 text-2xl font-black tracking-tight sm:text-3xl"
                    >
                        Kelola Akun Pengurus
                    </h2>

                    <p class="mt-2 max-w-xl text-sm leading-6 text-blue-50/90">
                        Tambahkan, ubah, reset password, dan hapus akun pengurus
                        koperasi melalui satu halaman yang terintegrasi.
                    </p>
                </div>
            </section>
        </Reveal>
        <!-- Toolbar -->
        <Reveal direction="right" :duration="700">
            <section
                class="mt-6 rounded-3xl border border-blue-100 bg-white p-5 shadow-[0_8px_28px_rgba(26,111,189,0.08)]"
            >
                <div
                    class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
                >
                    <form
                        class="flex flex-1 flex-col gap-2 sm:flex-row"
                        @submit.prevent="submitSearch"
                    >
                        <div class="relative max-w-lg flex-1">
                            <svg
                                class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <circle cx="11" cy="11" r="8" />

                                <path d="m21 21-4.35-4.35" />
                            </svg>

                            <input
                                v-model="search"
                                type="search"
                                placeholder="Cari nama atau username..."
                                class="w-full rounded-xl border border-blue-100 bg-[#f8fbff] py-3 pl-10 pr-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#1a6fbd] focus:bg-white focus:ring-4 focus:ring-blue-100"
                            />
                        </div>

                        <button
                            type="submit"
                            class="rounded-xl bg-blue-50 px-4 py-3 text-sm font-bold text-[#1a6fbd] transition hover:-translate-y-0.5 hover:bg-blue-100"
                        >
                            Cari
                        </button>

                        <button
                            v-if="search"
                            type="button"
                            class="rounded-xl px-4 py-3 text-sm font-bold text-slate-400 transition hover:bg-slate-50 hover:text-slate-600"
                            @click="resetSearch"
                        >
                            Reset
                        </button>
                    </form>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#1a6fbd] to-[#0f4f8e] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-200 transition duration-200 hover:-translate-y-0.5 hover:shadow-xl"
                        @click="openCreateModal"
                    >
                        <span class="text-lg leading-none"> + </span>

                        Tambah Pengurus
                    </button>
                </div>
            </section>
        </Reveal>
        <!-- Data table -->
        <Reveal direction="up" :duration="700">
            <section
                class="mt-5 overflow-hidden rounded-3xl border border-blue-100 bg-white shadow-[0_8px_28px_rgba(26,111,189,0.08)]"
            >
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left">
                        <thead
                            class="border-b border-blue-100 bg-gradient-to-r from-blue-50 to-green-50"
                        >
                            <tr>
                                <th
                                    class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500"
                                >
                                    No.
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500"
                                >
                                    Pengurus
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500"
                                >
                                    Username
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500"
                                >
                                    Status Password
                                </th>

                                <th
                                    class="px-6 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-blue-50">
                            <tr
                                v-for="(item, index) in pengurus.data"
                                :key="item.id"
                                class="transition duration-200 hover:bg-blue-50/50"
                            >
                                <td
                                    class="px-6 py-4 text-sm font-semibold text-slate-400"
                                >
                                    {{ pengurus.from + index }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-[#1a6fbd] to-[#3aab2e] text-sm font-black text-white shadow-md shadow-blue-100"
                                        >
                                            {{
                                                item.name
                                                    .charAt(0)
                                                    .toUpperCase()
                                            }}
                                        </div>

                                        <div>
                                            <p
                                                class="text-sm font-bold text-slate-700"
                                            >
                                                {{ item.name }}
                                            </p>

                                            <p class="text-xs text-slate-400">
                                                Pengurus koperasi
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-bold text-[#1a6fbd]"
                                    >
                                        {{ item.username }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold"
                                        :class="
                                            item.must_change_password
                                                ? 'bg-orange-50 text-[#f07c1a]'
                                                : 'bg-green-50 text-[#268c1a]'
                                        "
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full"
                                            :class="
                                                item.must_change_password
                                                    ? 'bg-[#f07c1a]'
                                                    : 'bg-[#3aab2e]'
                                            "
                                        />

                                        {{
                                            item.must_change_password
                                                ? "Password sementara"
                                                : "Password aktif"
                                        }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="rounded-lg border border-blue-100 px-3 py-2 text-xs font-bold text-[#1a6fbd] transition hover:-translate-y-0.5 hover:bg-blue-50"
                                            @click="openEditModal(item)"
                                        >
                                            Ubah
                                        </button>

                                        <button
                                            type="button"
                                            class="rounded-lg border border-orange-100 px-3 py-2 text-xs font-bold text-[#f07c1a] transition hover:-translate-y-0.5 hover:bg-orange-50"
                                            @click="resetPassword(item)"
                                        >
                                            Reset Password
                                        </button>

                                        <button
                                            type="button"
                                            class="rounded-lg border border-red-100 px-3 py-2 text-xs font-bold text-red-500 transition hover:-translate-y-0.5 hover:bg-red-50"
                                            @click="openDeleteModal(item)"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="pengurus.data.length === 0">
                                <td colspan="5" class="px-6 py-14 text-center">
                                    <div
                                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-2xl"
                                    >
                                        👥
                                    </div>

                                    <p
                                        class="mt-4 text-sm font-bold text-slate-600"
                                    >
                                        Data pengurus belum tersedia
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        Tambahkan akun pengurus koperasi untuk
                                        memulai.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :pagination="pengurus" />
            </section>

            <!-- Modal tambah pengurus -->
            <PengurusFormModal
                :show="showCreateModal"
                :form="createForm"
                mode="create"
                @close="closeCreateModal"
                @submit="submitCreate"
            />

            <!-- Modal ubah pengurus -->
            <PengurusFormModal
                :show="showEditModal"
                :form="editForm"
                mode="edit"
                @close="closeEditModal"
                @submit="submitEdit"
            />

            <!-- Modal konfirmasi hapus -->
            <ConfirmModal
                :show="showDeleteModal"
                title="Hapus Pengurus?"
                :message="`Data pengurus ${selectedPengurus?.name ?? ''} akan dihapus dari sistem.`"
                confirm-text="Hapus"
                :processing="deleteProcessing"
                variant="danger"
                @close="closeDeleteModal"
                @confirm="submitDelete"
            />

            <!-- Modal password sementara -->
            <TemporaryPasswordModal
                :show="showPasswordModal"
                :credential="temporaryPassword"
                @close="closePasswordModal"
            />
        </Reveal>
    </AuthenticatedLayout>
</template>
