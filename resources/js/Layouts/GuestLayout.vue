<script setup>
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div class="ds-auth-root">
        <!-- Background blobs -->
        <div class="ds-auth-blob ds-auth-blob--1" />
        <div class="ds-auth-blob ds-auth-blob--2" />
        <div class="ds-auth-blob ds-auth-blob--3" />

        <!-- Back to home link -->
        <Link href="/" class="ds-auth-back">
            <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
            <span>Kembali</span>
        </Link>

        <!-- Main container -->
        <div class="ds-auth-container">
            <div class="ds-auth-card">
                <!-- Logo -->
                <div class="ds-auth-logo-wrap">
                    <div class="ds-nav__logo-wrap">
                        <img
                            src="/images/logo.png"
                            alt="DigiSejahtera"
                            class="ds-nav__logo-img"
                        />
                    </div>
                    <div class="ds-auth-logo">
                        <span class="ds-brand-digi">Digi</span
                        ><span class="ds-brand-sejahtera">Sejahtera</span>
                    </div>
                    <p class="ds-auth-tagline">Sistem Koperasi Digital</p>
                </div>

                <!-- Slot content -->
                <div class="ds-auth-form">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.ds-auth-root {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    position: relative;
    overflow: hidden;
    background: linear-gradient(160deg, #f0f7ff 0%, #e8f5e9 50%, #fff8ee 100%);
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
    --shadow-sm: 0 2px 8px rgba(26, 111, 189, 0.1);
    --shadow-md: 0 6px 24px rgba(26, 111, 189, 0.14);
    --shadow-lg: 0 16px 48px rgba(26, 111, 189, 0.18);
    --radius: 16px;
    font-family: "Segoe UI", "Noto Sans", Arial, sans-serif;
}

/* Blobs */
.ds-auth-blob {
    position: fixed;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.3;
    pointer-events: none;
    z-index: 0;
}
.ds-auth-blob--1 {
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, var(--blue-light), transparent);
    top: -100px;
    left: -100px;
    animation: blobFloat1 8s ease-in-out infinite;
}
.ds-auth-blob--2 {
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, var(--green-light), transparent);
    bottom: -50px;
    right: -50px;
    animation: blobFloat2 10s ease-in-out infinite;
}
.ds-auth-blob--3 {
    width: 250px;
    height: 250px;
    background: radial-gradient(circle, var(--orange-light), transparent);
    top: 50%;
    right: 10%;
    animation: blobFloat1 12s ease-in-out infinite reverse;
}

@keyframes blobFloat1 {
    0%,
    100% {
        transform: translate(0, 0) scale(1);
    }
    50% {
        transform: translate(30px, 20px) scale(1.05);
    }
}
@keyframes blobFloat2 {
    0%,
    100% {
        transform: translate(0, 0) scale(1);
    }
    50% {
        transform: translate(-20px, -30px) scale(1.08);
    }
}

.ds-auth-back {
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 10;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--gray-700);
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 600;
    transition: all 0.2s ease;
}
.ds-auth-back:hover {
    color: var(--blue);
    transform: translateX(-4px);
}

.ds-auth-container {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 440px;
    animation: fadeInUp 0.6s ease both;
}

.ds-auth-card {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    padding: 48px 32px;
    border: 1px solid var(--gray-100);
}

.ds-auth-logo-wrap {
    text-align: center;
    margin-bottom: 36px;
}

.ds-auth-logo {
    font-size: 1.8rem;
    font-weight: 900;
    letter-spacing: -0.5px;
    margin-bottom: 8px;
}

.ds-brand-digi {
    color: var(--blue);
}

.ds-brand-sejahtera {
    color: var(--green);
}

.ds-auth-tagline {
    font-size: 0.8rem;
    color: var(--gray-500);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 0;
}

.ds-auth-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.ds-nav__logo-wrap {
    width: 80px;
    height: 80px;
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    background: linear-gradient(135deg, rgba(26, 111, 189, 0.08), rgba(58, 171, 46, 0.08));
    border: 2px solid var(--gray-100);
    transition: all 0.3s ease;
}

.ds-nav__logo-wrap:hover {
    border-color: var(--blue-light);
    box-shadow: var(--shadow-sm);
}

.ds-nav__logo-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 8px;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(24px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 640px) {
    .ds-auth-card {
        padding: 36px 24px;
    }
    .ds-auth-back {
        top: 16px;
        left: 16px;
    }
}
</style>
