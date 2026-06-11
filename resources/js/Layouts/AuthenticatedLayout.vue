<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, ref } from "vue";

const page = usePage();

const sidebarOpen = ref(false);
const profileMenuOpen = ref(false);

const user = computed(() => {
    return page.props.auth?.user;
});

const menus = [
    {
        label: "Dashboard",
        href: "/dashboard",
        icon: "dashboard",
    },
    {
        label: "Kartu Rekening",
        href: "/kartu-rekening",
        icon: "card",
    },
    {
        label: "Data Pengurus",
        href: "/pengurus",
        icon: "users",
        adminOnly: true,
    },
    {
        label: "Kitir",
        href: "/kitir",
        icon: "report",
    },
    {
        label: "Laporan",
        href: "/laporan",
        icon: "report",
    },
    {
        label: "Riwayat Transaksi",
        href: "/riwayat-transaksi",
        icon: "history",
    },
];

const visibleMenus = computed(() => {
    return menus.filter((menu) => {
        return !menu.adminOnly || user.value?.role === "admin";
    });
});

const isActive = (href) => {
    return page.url.startsWith(href);
};

const closeProfileMenu = (event) => {
    if (!event.target.closest("[data-profile-menu]")) {
        profileMenuOpen.value = false;
    }
};

onMounted(() => {
    window.addEventListener("click", closeProfileMenu);
});

onUnmounted(() => {
    window.removeEventListener("click", closeProfileMenu);
});
</script>

<template>
    <div class="min-h-screen bg-[#f4f8ff] text-slate-800">
        <!-- Overlay sidebar mobile -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            leave-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <button
                v-if="sidebarOpen"
                type="button"
                class="fixed inset-0 z-30 bg-slate-950/30 backdrop-blur-sm lg:hidden"
                aria-label="Tutup sidebar"
                @click="sidebarOpen = false"
            />
        </Transition>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-72 flex-col border-r border-blue-100 bg-white/95 shadow-[0_10px_40px_rgba(26,111,189,0.12)] backdrop-blur-xl transition-transform duration-300 lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div
                class="flex h-20 items-center gap-3 border-b border-blue-50 px-6"
            >
                <img
                    src="/images/logo.webp"
                    alt="Logo DigiSejahtera"
                    class="h-12 w-12 object-contain drop-shadow-sm"
                />

                <div>
                    <p class="text-xl font-black tracking-tight">
                        <span class="text-[#1a6fbd]">Digi</span>
                        <span class="text-[#3aab2e]">Sejahtera</span>
                    </p>

                    <p
                        class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400"
                    >
                        Koperasi Digital
                    </p>
                </div>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-6">
                <p
                    class="mb-3 px-3 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400"
                >
                    Menu Utama
                </p>

                <Link
                    v-for="menu in visibleMenus"
                    :key="menu.href"
                    :href="menu.href"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition duration-200"
                    :class="
                        isActive(menu.href)
                            ? 'bg-gradient-to-r from-[#1a6fbd] to-[#0f4f8e] text-white shadow-lg shadow-blue-200'
                            : 'text-slate-500 hover:bg-blue-50 hover:text-[#1a6fbd]'
                    "
                    @click="sidebarOpen = false"
                >
                    <!-- Dashboard -->
                    <svg
                        v-if="menu.icon === 'dashboard'"
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <rect x="3" y="3" width="7" height="7" rx="1" />
                        <rect x="14" y="3" width="7" height="7" rx="1" />
                        <rect x="3" y="14" width="7" height="7" rx="1" />
                        <rect x="14" y="14" width="7" height="7" rx="1" />
                    </svg>

                    <!-- Card -->
                    <svg
                        v-else-if="menu.icon === 'card'"
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <rect x="3" y="5" width="18" height="14" rx="2" />
                        <path d="M3 10h18" />
                    </svg>

                    <!-- Users -->
                    <svg
                        v-else-if="menu.icon === 'users'"
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>

                    <!-- Report -->
                    <svg
                        v-else
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                        <path
                            d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"
                        />
                    </svg>

                    <span>{{ menu.label }}</span>
                </Link>
            </nav>

            <div class="border-t border-blue-50 p-4">
                <div
                    class="rounded-2xl bg-gradient-to-br from-blue-50 to-green-50 p-4"
                >
                    <p class="text-sm font-bold text-slate-700">
                        {{ user?.name }}
                    </p>

                    <p class="mt-0.5 text-xs capitalize text-slate-500">
                        {{ user?.role }}
                    </p>

                    <Link
                        :href="route('profile.edit')"
                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl border border-blue-100 bg-white px-3 py-2 text-xs font-bold text-[#1a6fbd] transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-sm"
                    >
                        Lihat Profil
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="lg:pl-72">
            <header
                class="sticky top-0 z-20 border-b border-blue-100 bg-white/80 backdrop-blur-xl"
            >
                <div
                    class="flex h-20 items-center justify-between px-5 sm:px-8"
                >
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="rounded-xl border border-blue-100 bg-white p-2 text-[#1a6fbd] shadow-sm lg:hidden"
                            aria-label="Buka sidebar"
                            @click="sidebarOpen = true"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.18em] text-[#3aab2e]"
                            >
                                <span class="text-[#1a6fbd]">Digi</span>
                                <span class="text-[#3aab2e]">Sejahtera</span>
                            </p>

                            <h1
                                class="text-lg font-black tracking-tight text-slate-800"
                            >
                                <slot name="title" />
                            </h1>
                        </div>
                    </div>

                    <!-- Navbar profile dropdown -->
                    <div class="relative" data-profile-menu>
                        <button
                            type="button"
                            class="flex items-center gap-3 rounded-2xl px-2 py-2 transition hover:bg-blue-50"
                            @click.stop="profileMenuOpen = !profileMenuOpen"
                        >
                            <div class="hidden text-right sm:block">
                                <p class="text-sm font-bold text-slate-700">
                                    {{ user?.name }}
                                </p>

                                <p class="text-xs capitalize text-slate-400">
                                    {{ user?.role }}
                                </p>
                            </div>

                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-[#1a6fbd] to-[#3aab2e] text-sm font-black text-white shadow-md shadow-blue-200"
                            >
                                {{ user?.name?.charAt(0)?.toUpperCase() }}
                            </div>

                            <svg
                                class="h-4 w-4 text-slate-400 transition"
                                :class="profileMenuOpen ? 'rotate-180' : ''"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <Transition
                            enter-active-class="transition duration-150 ease-out"
                            leave-active-class="transition duration-100 ease-in"
                            enter-from-class="-translate-y-2 opacity-0"
                            leave-to-class="-translate-y-2 opacity-0"
                        >
                            <div
                                v-if="profileMenuOpen"
                                class="absolute right-0 mt-2 w-56 overflow-hidden rounded-2xl border border-blue-100 bg-white p-2 shadow-xl shadow-blue-100/70"
                            >
                                <div class="border-b border-blue-50 px-3 py-3">
                                    <p
                                        class="truncate text-sm font-bold text-slate-700"
                                    >
                                        {{ user?.name }}
                                    </p>

                                    <p class="truncate text-xs text-slate-400">
                                        @{{ user?.username }}
                                    </p>
                                </div>

                                <Link
                                    :href="route('profile.edit')"
                                    class="mt-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-500 transition hover:bg-blue-50 hover:text-[#1a6fbd]"
                                    @click="profileMenuOpen = false"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <circle cx="12" cy="8" r="4" />
                                        <path d="M4 21a8 8 0 0 1 16 0" />
                                    </svg>

                                    Profil Saya
                                </Link>

                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-red-500 transition hover:bg-red-50"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path d="M10 17l5-5-5-5" />
                                        <path d="M15 12H3" />
                                        <path d="M21 19V5a2 2 0 0 0-2-2h-6" />
                                    </svg>

                                    Keluar
                                </Link>
                            </div>
                        </Transition>
                    </div>
                </div>
            </header>

            <main class="px-5 py-7 sm:px-8">
                <slot />
            </main>
        </div>
    </div>
</template>
