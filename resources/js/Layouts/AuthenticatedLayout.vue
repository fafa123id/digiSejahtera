<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const scrollY = ref(0);
const isScrolled = ref(false);

const handleScroll = () => {
  scrollY.value = window.scrollY;
  isScrolled.value = window.scrollY > 60;
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div class="authenticated-layout">
        <!-- ═══════════════════ NAVBAR ═══════════════════ -->
        <nav :class="['ds-nav', { 'ds-nav--scrolled': isScrolled }]">
            <div class="ds-nav__inner">
                <div class="ds-nav__brand">
                    <div class="ds-nav__logo-wrap">
                        <img src="/images/logo.png" alt="DigiSejahtera" class="ds-nav__logo-img" />
                    </div>
                    <Link :href="route('dashboard')" class="ds-nav__brand-text">
                        <span class="ds-brand-digi">Digi</span><span class="ds-brand-sejahtera">Sejahtera</span>
                    </Link>
                </div>

                <!-- Desktop Navigation -->
                <div class="ds-nav__desktop-menu">
                    <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                        Dashboard
                    </NavLink>
                </div>

                <!-- User Dropdown for Desktop -->
                <div class="ds-nav__user-dropdown">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <div class="ds-nav__user-button">
                                <span>{{ $page.props.auth.user.name }}</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2a6.5 6.5 0 0 0-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4 3 5.5M5 16a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2" />
                                </svg>
                            </div>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.edit') " :active="route().current('profile.edit')">
                                Profile
                            </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">
                                Keluar
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>

                <!-- Hamburger -->
                <div class="ds-nav__hamburger-wrapper">
                    <button
                        @click="showingNavigationDropdown = !showingNavigationDropdown"
                        class="ds-nav__hamburger"
                    >
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Responsive Navigation Menu -->
        <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }" class="ds-nav__responsive sm:hidden">
            <div class="ds-nav__responsive-content">
                <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                    Dashboard
                </ResponsiveNavLink>

                <!-- Responsive Settings Options -->
                <div class="ds-nav__responsive-settings">
                    <div class="ds-nav__responsive-user">
                        <div class="ds-nav__responsive-user-name">{{ $page.props.auth.user.name }}</div>
                        <div class="ds-nav__responsive-user-email">{{ $page.props.auth.user.email }}</div>
                    </div>

                    <div class="ds-nav__responsive-menu">
                        <ResponsiveNavLink :href="route('profile.edit')" :active="route().current('profile.edit')">
                            Profile
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                            Keluar
                        </ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-gray-100" style="padding-top: 72px;">
            <!-- Page Heading -->
            <header
                class="bg-white shadow"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
/* ═══ CSS VARIABLES ═══ */
.authenticated-layout {
  --blue: #1a6fbd;
  --blue-dark: #0f4f8e;
  --blue-light: #3d8fd4;
  --green: #3aab2e;
  --green-dark: #268c1a;
  --green-light: #5cc94f;
  --orange: #f07c1a;
  --orange-light: #f9a54a;
  --teal: #17a087;
  --white: #ffffff;
  --off-white: #f4f8ff;
  --gray-100: #eef2f9;
  --gray-200: #dde4f0;
  --gray-500: #7a8aad;
  --gray-700: #3a4a6b;
  --gray-900: #14213d;
  --shadow-sm: 0 2px 8px rgba(26, 111, 189, 0.10);
  --shadow-md: 0 6px 24px rgba(26, 111, 189, 0.14);
  --shadow-lg: 0 16px 48px rgba(26, 111, 189, 0.18);
  --radius: 16px;
  --radius-sm: 8px;
  --radius-lg: 24px;
}

/* ═══ NAV ═══ */
.ds-nav {
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 100;
  transition: all 0.3s ease;
  padding: 0 24px;
  background: var(--white);
}

.ds-nav__inner {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 72px;
}

.ds-nav--scrolled {
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(16px);
  box-shadow: var(--shadow-sm);
}

.ds-nav__brand {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  cursor: pointer;
}

.ds-nav__logo-wrap {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.ds-nav__logo-img {
  width: 40px;
  height: 40px;
  object-fit: contain;
}

.ds-nav__brand-text {
  font-size: 1.4rem;
  font-weight: 800;
  letter-spacing: -0.5px;
  text-decoration: none;
  color: inherit;
  display: flex;
  gap: 0;
}

.ds-brand-digi { color: var(--blue); }
.ds-brand-sejahtera { color: var(--green); }

/* Desktop Navigation */
.ds-nav__desktop-menu {
  display: none;
  align-items: center;
  gap: 8px;
  flex: 1;
  margin-left: 32px;
}

@media (min-width: 640px) {
  .ds-nav__desktop-menu {
    display: flex;
  }
}

/* User Dropdown Button */
.ds-nav__user-dropdown {
  display: none;
}

@media (min-width: 640px) {
  .ds-nav__user-dropdown {
    display: flex;
    align-items: center;
  }
}

.ds-nav__user-button {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, var(--blue), var(--blue-dark));
  color: white;
  text-decoration: none;
  padding: 10px 22px;
  border-radius: 50px;
  font-weight: 700;
  font-size: 0.95rem;
  transition: all 0.22s ease;
  box-shadow: 0 4px 14px rgba(26,111,189,0.3);
  cursor: pointer;
  border: none;
}

.ds-nav__user-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(26,111,189,0.4);
  background: linear-gradient(135deg, var(--blue-dark), #0a3d70);
}

/* Hamburger */
.ds-nav__hamburger-wrapper {
  display: flex;
  align-items: center;
}

@media (min-width: 640px) {
  .ds-nav__hamburger-wrapper {
    display: none;
  }
}

.ds-nav__hamburger {
  background: none;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 8px;
  color: var(--gray-700);
  transition: all 0.2s ease;
}

.ds-nav__hamburger:hover {
  color: var(--blue);
}

/* Responsive Menu */
.ds-nav__responsive {
  background: white;
  border-bottom: 1px solid var(--gray-200);
  transition: all 0.3s ease;
  max-height: 500px;
  overflow-y: auto;
}

.ds-nav__responsive-content {
  padding: 12px 0;
  display: flex;
  flex-direction: column;
  gap: 0;
}

.ds-nav__responsive-settings {
  border-top: 2px solid var(--gray-200);
  padding: 16px 0;
  margin: 12px 0 0 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.ds-nav__responsive-user {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 0 16px;
}

.ds-nav__responsive-user-name {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--gray-900);
}

.ds-nav__responsive-user-email {
  font-size: 0.8rem;
  color: var(--gray-500);
}

.ds-nav__responsive-menu {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 0;
}

/* ═══ ANIMATIONS ═══ */
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(24px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInRight {
  from { opacity: 0; transform: translateX(40px); }
  to { opacity: 1; transform: translateX(0); }
}

/* ═══ RESPONSIVE ═══ */
@media (max-width: 768px) {
  .ds-nav__inner {
    padding: 0;
  }

  .ds-nav {
    padding: 0 16px;
  }
}
</style>
