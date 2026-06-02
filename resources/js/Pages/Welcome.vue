<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'

const scrollY = ref(0)
const isScrolled = ref(false)
const activeFeature = ref(0)

const features = [
  {
    icon: '💰',
    title: 'Simpanan',
    desc: 'Kelola simpanan pokok, wajib, dan sukarela setiap anggota secara terintegrasi dan real-time.',
  },
  {
    icon: '🏦',
    title: 'Pinjaman',
    desc: 'Proses pengajuan dan pencatatan pinjaman anggota dengan perhitungan jasa otomatis.',
  },
  {
    icon: '📋',
    title: 'Angsuran',
    desc: 'Catat dan pantau angsuran tiap anggota beserta sisa pinjaman secara akurat.',
  },
  {
    icon: '📊',
    title: 'Laporan Keuangan',
    desc: 'Laporan simpanan, pinjaman, SHU, dan RAT siap cetak kapan saja dengan satu klik.',
  },
  {
    icon: '🪪',
    title: 'Kartu Rekening & KITIR',
    desc: 'Tampilkan dan cetak kartu rekening serta KITIR per anggota dengan mudah.',
  },
  {
    icon: '📈',
    title: 'Dashboard Koperasi',
    desc: 'Pantau kondisi keuangan koperasi secara menyeluruh melalui dashboard informatif.',
  },
]

const stats = [
  { value: '100%', label: 'Terintegrasi' },
  { value: 'Real-time', label: 'Data Terkini' },
  { value: 'Otomatis', label: 'Perhitungan' },
  { value: 'Aman', label: 'Data Tersimpan' },
]

const handleScroll = () => {
  scrollY.value = window.scrollY
  isScrolled.value = window.scrollY > 60
}

let featureTimer
onMounted(() => {
  window.addEventListener('scroll', handleScroll)
  featureTimer = setInterval(() => {
    activeFeature.value = (activeFeature.value + 1) % features.length
  }, 3000)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  clearInterval(featureTimer)
})
</script>

<template>
  <Head title="Selamat Datang — DigiSejahtera" />

  <div class="ds-root">
    <!-- ═══════════════════ NAVBAR ═══════════════════ -->
    <nav :class="['ds-nav', { 'ds-nav--scrolled': isScrolled }]">
      <div class="ds-nav__inner">
        <div class="ds-nav__brand">
          <div class="ds-nav__logo-wrap">
            <img src="/images/logo.png" alt="DigiSejahtera" class="ds-nav__logo-img" />
          </div>
          <span class="ds-nav__brand-text">
            <span class="ds-brand-digi">Digi</span><span class="ds-brand-sejahtera">Sejahtera</span>
          </span>
        </div>
        <Link href="/login" class="ds-nav__login-btn">
          <span>Masuk</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3" />
          </svg>
        </Link>
      </div>
    </nav>

    <!-- ═══════════════════ HERO ═══════════════════ -->
    <section class="ds-hero">
      <!-- Decorative blobs -->
      <div class="ds-blob ds-blob--1" />
      <div class="ds-blob ds-blob--2" />
      <div class="ds-blob ds-blob--3" />

      <!-- Floating pixel dots (inspired by logo) -->
      <div class="ds-pixels">
        <span v-for="i in 12" :key="i" class="ds-pixel" :style="{ '--i': i }" />
      </div>

      <div class="ds-hero__content">
        <div class="ds-hero__badge">
          <span class="ds-badge-dot" />
          Sistem Koperasi Digital
        </div>

        <h1 class="ds-hero__title">
          Selamat Datang di<br />
          <span class="ds-hero__title--gradient">DigiSejahtera</span>
        </h1>

        <p class="ds-hero__sub">
          Platform manajemen keuangan koperasi simpan pinjam yang terintegrasi,
          otomatis, dan mudah digunakan. Diciptakan untuk Koperasi Guru SMPN 1 Grogol, Kediri.
        </p>

        <div class="ds-hero__actions">
          <Link href="/login" class="ds-btn ds-btn--primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
              <path d="M7 11V7a5 5 0 0 1 10 0v4" />
            </svg>
            Masuk ke Sistem
          </Link>
          <a href="#fitur" class="ds-btn ds-btn--ghost">
            Lihat Fitur
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M12 5v14M5 12l7 7 7-7" />
            </svg>
          </a>
        </div>

        <!-- Stats strip -->
        <div class="ds-stats">
          <div v-for="stat in stats" :key="stat.label" class="ds-stat">
            <span class="ds-stat__value">{{ stat.value }}</span>
            <span class="ds-stat__label">{{ stat.label }}</span>
          </div>
        </div>
      </div>

      <!-- Hero illustration -->
      <div class="ds-hero__illustration">
        <div class="ds-card-mockup">
          <div class="ds-mockup__header">
            <div class="ds-mockup__dots">
              <span /><span /><span />
            </div>
            <span class="ds-mockup__title">Dashboard Koperasi</span>
          </div>
          <div class="ds-mockup__body">
            <div class="ds-mockup__row">
              <div class="ds-mockup__stat-box ds-mockup__stat-box--blue">
                <span class="ds-mockup__stat-label">Total Simpanan</span>
                <span class="ds-mockup__stat-val">Rp 48.200.000</span>
                <span class="ds-mockup__stat-badge">+2.4%</span>
              </div>
              <div class="ds-mockup__stat-box ds-mockup__stat-box--green">
                <span class="ds-mockup__stat-label">Pinjaman Aktif</span>
                <span class="ds-mockup__stat-val">Rp 32.750.000</span>
                <span class="ds-mockup__stat-badge">24 anggota</span>
              </div>
            </div>
            <div class="ds-mockup__row">
              <div class="ds-mockup__stat-box ds-mockup__stat-box--orange">
                <span class="ds-mockup__stat-label">SHU Periode Ini</span>
                <span class="ds-mockup__stat-val">Rp 5.180.000</span>
                <span class="ds-mockup__stat-badge">2024/2025</span>
              </div>
              <div class="ds-mockup__stat-box ds-mockup__stat-box--teal">
                <span class="ds-mockup__stat-label">Total Anggota</span>
                <span class="ds-mockup__stat-val">42 Guru</span>
                <span class="ds-mockup__stat-badge">Aktif</span>
              </div>
            </div>
            <div class="ds-mockup__bar-area">
              <span class="ds-mockup__bar-title">Angsuran Bulan Ini</span>
              <div class="ds-mockup__bars">
                <div v-for="(h, i) in [55, 80, 45, 90, 65, 75, 50]" :key="i"
                  class="ds-mockup__bar"
                  :style="{ height: h + '%', animationDelay: (i * 0.1) + 's' }" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════ FITUR ═══════════════════ -->
    <section id="fitur" class="ds-features">
      <div class="ds-section-inner">
        <div class="ds-section-header">
          <span class="ds-section-tag">Fitur Unggulan</span>
          <h2 class="ds-section-title">Semua yang Dibutuhkan<br /><em>Koperasi Guru</em></h2>
          <p class="ds-section-desc">
            Satu platform untuk mengelola seluruh aktivitas keuangan koperasi — dari simpan pinjam
            hingga laporan periodik — secara cepat, akurat, dan transparan.
          </p>
        </div>

        <div class="ds-features__grid">
          <div
            v-for="(feature, i) in features"
            :key="i"
            class="ds-feature-card"
            :class="{ 'ds-feature-card--active': activeFeature === i }"
            @mouseenter="activeFeature = i"
          >
            <div class="ds-feature-card__icon">{{ feature.icon }}</div>
            <h3 class="ds-feature-card__title">{{ feature.title }}</h3>
            <p class="ds-feature-card__desc">{{ feature.desc }}</p>
            <div class="ds-feature-card__line" />
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════ HOW IT WORKS ═══════════════════ -->
    <section class="ds-how">
      <div class="ds-section-inner">
        <div class="ds-section-header">
          <span class="ds-section-tag">Cara Penggunaan</span>
          <h2 class="ds-section-title">Mulai Dalam<br /><em>Tiga Langkah</em></h2>
        </div>
        <div class="ds-steps">
          <div class="ds-step">
            <div class="ds-step__num">01</div>
            <div class="ds-step__content">
              <h3>Masuk ke Sistem</h3>
              <p>Gunakan akun yang telah disiapkan pengurus untuk mengakses dashboard DigiSejahtera.</p>
            </div>
          </div>
          <div class="ds-step__connector" />
          <div class="ds-step">
            <div class="ds-step__num">02</div>
            <div class="ds-step__content">
              <h3>Kelola Transaksi</h3>
              <p>Input simpanan, pinjaman, dan angsuran anggota dengan mudah melalui antarmuka yang intuitif.</p>
            </div>
          </div>
          <div class="ds-step__connector" />
          <div class="ds-step">
            <div class="ds-step__num">03</div>
            <div class="ds-step__content">
              <h3>Cetak Laporan</h3>
              <p>Hasilkan laporan keuangan, KITIR, kartu rekening, dan SHU dalam hitungan detik.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════ CTA ═══════════════════ -->
    <section class="ds-cta">
      <div class="ds-cta__blob" />
      <div class="ds-cta__inner">
        <div class="ds-cta__logo-wrap">
          <img src="/images/logo.png" alt="DigiSejahtera" class="ds-cta__logo" />
        </div>
        <h2 class="ds-cta__title">Siap Mengelola Koperasi<br />dengan Lebih Cerdas?</h2>
        <p class="ds-cta__desc">
          Login sekarang dan mulai kelola keuangan koperasi Anda secara digital,
          efisien, dan akuntabel bersama DigiSejahtera.
        </p>
        <Link href="/login" class="ds-btn ds-btn--cta">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3" />
          </svg>
          Masuk Sekarang
        </Link>
        <p class="ds-cta__note">Hanya pengurus dan anggota terdaftar yang dapat masuk.</p>
      </div>
    </section>

    <!-- ═══════════════════ FOOTER ═══════════════════ -->
    <footer class="ds-footer">
      <div class="ds-footer__inner">
        <div class="ds-footer__brand">
          <span class="ds-brand-digi">Digi</span><span class="ds-brand-sejahtera">Sejahtera</span>
        </div>
        <p class="ds-footer__desc">
          Sistem Aplikasi Manajemen Keuangan Koperasi Simpan Pinjam<br />
          Koperasi Guru SMPN 1 Grogol, Kabupaten Kediri
        </p>
        <p class="ds-footer__copy">© {{ new Date().getFullYear() }} DigiSejahtera. Hak Cipta Dilindungi.</p>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* ═══ CSS VARIABLES ═══ */
.ds-root {
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

  font-family: 'Segoe UI', 'Noto Sans', Arial, sans-serif;
  background: var(--white);
  color: var(--gray-900);
  overflow-x: hidden;
}

/* ═══ NAV ═══ */
.ds-nav {
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 100;
  transition: all 0.3s ease;
  padding: 0 24px;
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
}

.ds-nav__logo-wrap {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
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
}

.ds-brand-digi { color: var(--blue); }
.ds-brand-sejahtera { color: var(--green); }

.ds-nav__login-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, var(--blue), var(--blue-dark));
  color: white;
  text-decoration: none;
  padding: 10px 22px;
  border-radius: 50px;
  font-weight: 600;
  font-size: 0.95rem;
  transition: all 0.2s ease;
  box-shadow: 0 4px 14px rgba(26,111,189,0.3);
}
.ds-nav__login-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(26,111,189,0.4);
}

/* ═══ HERO ═══ */
.ds-hero {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  padding: 100px 24px 60px;
  overflow: hidden;
  background: linear-gradient(160deg, #f0f7ff 0%, #e8f5e9 50%, #fff8ee 100%);
  gap: 48px;
  flex-wrap: wrap;
}

/* Blobs */
.ds-blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.35;
  pointer-events: none;
}
.ds-blob--1 {
  width: 500px; height: 500px;
  background: radial-gradient(circle, var(--blue-light), transparent);
  top: -100px; left: -100px;
  animation: blobFloat1 8s ease-in-out infinite;
}
.ds-blob--2 {
  width: 400px; height: 400px;
  background: radial-gradient(circle, var(--green-light), transparent);
  bottom: -80px; right: -80px;
  animation: blobFloat2 10s ease-in-out infinite;
}
.ds-blob--3 {
  width: 300px; height: 300px;
  background: radial-gradient(circle, var(--orange-light), transparent);
  top: 50%; right: 20%;
  animation: blobFloat1 12s ease-in-out infinite reverse;
}

@keyframes blobFloat1 {
  0%, 100% { transform: translate(0,0) scale(1); }
  50% { transform: translate(30px, 20px) scale(1.05); }
}
@keyframes blobFloat2 {
  0%, 100% { transform: translate(0,0) scale(1); }
  50% { transform: translate(-20px, -30px) scale(1.08); }
}

/* Pixels */
.ds-pixels { position: absolute; inset: 0; pointer-events: none; }
.ds-pixel {
  position: absolute;
  width: 8px; height: 8px;
  border-radius: 2px;
  opacity: 0;
  animation: pixelFade 4s ease-in-out infinite;
  animation-delay: calc(var(--i) * 0.3s);
}
.ds-pixel:nth-child(odd) { background: var(--blue); }
.ds-pixel:nth-child(even) { background: var(--green); }
.ds-pixel:nth-child(3n) { background: var(--orange); }
.ds-pixel:nth-child(1) { top: 15%; left: 8%; }
.ds-pixel:nth-child(2) { top: 25%; left: 12%; }
.ds-pixel:nth-child(3) { top: 10%; left: 20%; }
.ds-pixel:nth-child(4) { top: 70%; left: 5%; }
.ds-pixel:nth-child(5) { top: 80%; left: 15%; }
.ds-pixel:nth-child(6) { top: 20%; right: 8%; }
.ds-pixel:nth-child(7) { top: 15%; right: 18%; }
.ds-pixel:nth-child(8) { top: 60%; right: 6%; }
.ds-pixel:nth-child(9) { top: 75%; right: 12%; }
.ds-pixel:nth-child(10) { top: 40%; left: 3%; }
.ds-pixel:nth-child(11) { top: 50%; right: 4%; }
.ds-pixel:nth-child(12) { top: 35%; left: 90%; }

@keyframes pixelFade {
  0%, 100% { opacity: 0; transform: scale(0.5) rotate(0deg); }
  50% { opacity: 0.7; transform: scale(1) rotate(45deg); }
}

.ds-hero__content {
  position: relative;
  z-index: 2;
  max-width: 560px;
  flex: 1 1 340px;
}

.ds-hero__badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(26,111,189,0.08);
  border: 1px solid rgba(26,111,189,0.2);
  color: var(--blue);
  font-size: 0.85rem;
  font-weight: 600;
  padding: 6px 14px;
  border-radius: 50px;
  margin-bottom: 20px;
  animation: fadeInUp 0.6s ease both;
}
.ds-badge-dot {
  width: 8px; height: 8px;
  background: var(--green);
  border-radius: 50%;
  animation: pulse 2s infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(1.3); }
}

.ds-hero__title {
  font-size: clamp(2.2rem, 5vw, 3.4rem);
  font-weight: 900;
  line-height: 1.1;
  letter-spacing: -1.5px;
  color: var(--gray-900);
  margin: 0 0 20px;
  animation: fadeInUp 0.6s 0.1s ease both;
}
.ds-hero__title--gradient {
  background: linear-gradient(135deg, var(--blue), var(--green));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.ds-hero__sub {
  font-size: 1.05rem;
  color: var(--gray-700);
  line-height: 1.7;
  margin: 0 0 32px;
  animation: fadeInUp 0.6s 0.2s ease both;
}

.ds-hero__actions {
  display: flex;
  gap: 14px;
  flex-wrap: wrap;
  animation: fadeInUp 0.6s 0.3s ease both;
}

/* Buttons */
.ds-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  font-weight: 700;
  font-size: 0.95rem;
  border-radius: 50px;
  transition: all 0.22s ease;
  cursor: pointer;
  border: none;
}
.ds-btn--primary {
  background: linear-gradient(135deg, var(--blue), var(--blue-dark));
  color: white;
  padding: 14px 28px;
  box-shadow: 0 6px 20px rgba(26,111,189,0.35);
}
.ds-btn--primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 30px rgba(26,111,189,0.45);
}
.ds-btn--ghost {
  background: transparent;
  color: var(--gray-700);
  padding: 14px 20px;
  border: 2px solid var(--gray-200);
}
.ds-btn--ghost:hover {
  border-color: var(--blue);
  color: var(--blue);
  transform: translateY(-2px);
}
.ds-btn--cta {
  background: white;
  color: var(--blue-dark);
  padding: 16px 36px;
  font-size: 1rem;
  box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}
.ds-btn--cta:hover {
  transform: translateY(-3px);
  box-shadow: 0 14px 40px rgba(0,0,0,0.22);
}

/* Stats */
.ds-stats {
  display: flex;
  gap: 24px;
  flex-wrap: wrap;
  margin-top: 40px;
  padding-top: 28px;
  border-top: 1px solid var(--gray-200);
  animation: fadeInUp 0.6s 0.4s ease both;
}
.ds-stat { text-align: center; }
.ds-stat__value {
  display: block;
  font-size: 1.3rem;
  font-weight: 900;
  color: var(--blue);
  letter-spacing: -0.5px;
}
.ds-stat__label {
  font-size: 0.8rem;
  color: var(--gray-500);
  font-weight: 500;
}

/* ═══ CARD MOCKUP ═══ */
.ds-hero__illustration {
  position: relative;
  z-index: 2;
  flex: 1 1 320px;
  max-width: 440px;
  animation: fadeInRight 0.8s 0.3s ease both;
}

.ds-card-mockup {
  background: white;
  border-radius: 20px;
  box-shadow: var(--shadow-lg);
  overflow: hidden;
  border: 1px solid var(--gray-200);
}
.ds-mockup__header {
  background: linear-gradient(90deg, var(--blue-dark), var(--blue));
  padding: 14px 18px;
  display: flex;
  align-items: center;
  gap: 12px;
}
.ds-mockup__dots { display: flex; gap: 6px; }
.ds-mockup__dots span {
  width: 10px; height: 10px;
  border-radius: 50%;
  background: rgba(255,255,255,0.3);
}
.ds-mockup__dots span:nth-child(1) { background: #ff6b6b; }
.ds-mockup__dots span:nth-child(2) { background: #ffd93d; }
.ds-mockup__dots span:nth-child(3) { background: #6bcb77; }
.ds-mockup__title { color: white; font-size: 0.85rem; font-weight: 600; opacity: 0.9; }
.ds-mockup__body { padding: 18px; background: #f8faff; }
.ds-mockup__row { display: flex; gap: 12px; margin-bottom: 12px; }
.ds-mockup__stat-box {
  flex: 1;
  padding: 14px;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.ds-mockup__stat-box--blue { background: linear-gradient(135deg, #e8f3ff, #d0e8ff); border: 1px solid #b3d4f5; }
.ds-mockup__stat-box--green { background: linear-gradient(135deg, #e8f9e8, #d0f0d0); border: 1px solid #a8dba8; }
.ds-mockup__stat-box--orange { background: linear-gradient(135deg, #fff4e6, #ffe8c8); border: 1px solid #ffd49a; }
.ds-mockup__stat-box--teal { background: linear-gradient(135deg, #e6f9f7, #caf0eb); border: 1px solid #9adfd9; }
.ds-mockup__stat-label { font-size: 0.7rem; color: var(--gray-500); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.ds-mockup__stat-val { font-size: 0.9rem; font-weight: 800; color: var(--gray-900); }
.ds-mockup__stat-badge { font-size: 0.7rem; color: var(--green-dark); font-weight: 600; }
.ds-mockup__bar-area { background: white; border-radius: 12px; padding: 14px; border: 1px solid var(--gray-200); }
.ds-mockup__bar-title { font-size: 0.75rem; color: var(--gray-500); font-weight: 600; display: block; margin-bottom: 10px; }
.ds-mockup__bars { display: flex; align-items: flex-end; gap: 6px; height: 60px; }
.ds-mockup__bar {
  flex: 1;
  background: linear-gradient(180deg, var(--blue), var(--blue-light));
  border-radius: 4px 4px 0 0;
  animation: barGrow 0.8s ease both;
}
.ds-mockup__bar:nth-child(even) { background: linear-gradient(180deg, var(--green), var(--green-light)); }

@keyframes barGrow {
  from { height: 0 !important; }
}

/* ═══ FEATURES ═══ */
.ds-features {
  padding: 96px 24px;
  background: white;
}
.ds-section-inner { max-width: 1200px; margin: 0 auto; }

.ds-section-header {
  text-align: center;
  margin-bottom: 60px;
}
.ds-section-tag {
  display: inline-block;
  background: linear-gradient(135deg, rgba(26,111,189,0.1), rgba(58,171,46,0.1));
  color: var(--blue);
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  padding: 6px 16px;
  border-radius: 50px;
  border: 1px solid rgba(26,111,189,0.15);
  margin-bottom: 16px;
}
.ds-section-title {
  font-size: clamp(1.8rem, 4vw, 2.8rem);
  font-weight: 900;
  letter-spacing: -1px;
  color: var(--gray-900);
  line-height: 1.2;
  margin: 0 0 16px;
}
.ds-section-title em {
  font-style: normal;
  background: linear-gradient(135deg, var(--blue), var(--green));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.ds-section-desc {
  color: var(--gray-500);
  font-size: 1rem;
  max-width: 520px;
  margin: 0 auto;
  line-height: 1.7;
}

.ds-features__grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.ds-feature-card {
  background: var(--off-white);
  border-radius: var(--radius);
  padding: 28px;
  border: 2px solid transparent;
  transition: all 0.28s ease;
  position: relative;
  overflow: hidden;
  cursor: default;
}
.ds-feature-card::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: var(--radius);
  background: linear-gradient(135deg, rgba(26,111,189,0.04), rgba(58,171,46,0.04));
  opacity: 0;
  transition: opacity 0.3s;
}
.ds-feature-card:hover,
.ds-feature-card--active {
  border-color: var(--blue-light);
  box-shadow: var(--shadow-md);
  transform: translateY(-4px);
  background: white;
}
.ds-feature-card:hover::before,
.ds-feature-card--active::before { opacity: 1; }

.ds-feature-card__icon { font-size: 2.2rem; margin-bottom: 14px; }
.ds-feature-card__title {
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--gray-900);
  margin: 0 0 10px;
}
.ds-feature-card__desc {
  font-size: 0.9rem;
  color: var(--gray-500);
  line-height: 1.65;
  margin: 0;
}
.ds-feature-card__line {
  width: 32px;
  height: 3px;
  background: linear-gradient(90deg, var(--blue), var(--green));
  border-radius: 2px;
  margin-top: 18px;
  transition: width 0.3s;
}
.ds-feature-card:hover .ds-feature-card__line,
.ds-feature-card--active .ds-feature-card__line { width: 64px; }

/* ═══ HOW IT WORKS ═══ */
.ds-how {
  padding: 96px 24px;
  background: linear-gradient(160deg, #f0f7ff, #e8f5e9);
}
.ds-steps {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-wrap: wrap;
  gap: 16px;
}
.ds-step {
  background: white;
  border-radius: var(--radius);
  padding: 32px 28px;
  flex: 1 1 240px;
  max-width: 280px;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--gray-200);
  transition: all 0.25s ease;
}
.ds-step:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
.ds-step__num {
  font-size: 2.5rem;
  font-weight: 900;
  background: linear-gradient(135deg, var(--blue), var(--green));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 16px;
  line-height: 1;
}
.ds-step__content h3 {
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--gray-900);
  margin: 0 0 8px;
}
.ds-step__content p {
  font-size: 0.9rem;
  color: var(--gray-500);
  line-height: 1.65;
  margin: 0;
}
.ds-step__connector {
  width: 48px;
  height: 2px;
  background: linear-gradient(90deg, var(--blue-light), var(--green-light));
  border-radius: 2px;
  flex-shrink: 0;
}

/* ═══ CTA ═══ */
.ds-cta {
  padding: 100px 24px;
  background: linear-gradient(135deg, var(--blue-dark) 0%, var(--blue) 50%, var(--green-dark) 100%);
  position: relative;
  overflow: hidden;
  text-align: center;
}
.ds-cta__blob {
  position: absolute;
  width: 600px; height: 600px;
  border-radius: 50%;
  background: rgba(255,255,255,0.06);
  top: -200px; right: -200px;
  pointer-events: none;
}
.ds-cta__inner {
  position: relative;
  z-index: 2;
  max-width: 600px;
  margin: 0 auto;
}
.ds-cta__logo-wrap {
  width: 80px; height: 80px;
  margin: 0 auto 24px;
  border-radius: 20px;
  background: rgba(255,255,255,0.15);
  display: flex;
  align-items: center;
  justify-content: center;
}
.ds-cta__logo { width: 70px; height: 70px; object-fit: contain; }
.ds-cta__title {
  font-size: clamp(1.8rem, 4vw, 2.6rem);
  font-weight: 900;
  color: white;
  margin: 0 0 16px;
  line-height: 1.2;
  letter-spacing: -1px;
}
.ds-cta__desc {
  font-size: 1rem;
  color: rgba(255,255,255,0.8);
  line-height: 1.7;
  margin: 0 0 36px;
}
.ds-cta__note {
  margin-top: 16px;
  font-size: 0.82rem;
  color: rgba(255,255,255,0.55);
}

/* ═══ FOOTER ═══ */
.ds-footer {
  background: var(--gray-900);
  padding: 48px 24px;
  text-align: center;
}
.ds-footer__inner { max-width: 600px; margin: 0 auto; }
.ds-footer__brand {
  font-size: 1.5rem;
  font-weight: 900;
  letter-spacing: -0.5px;
  margin-bottom: 12px;
  display: block;
}
.ds-footer .ds-brand-digi { color: #6db8f5; }
.ds-footer .ds-brand-sejahtera { color: #7edd7a; }
.ds-footer__desc {
  font-size: 0.88rem;
  color: rgba(255,255,255,0.45);
  line-height: 1.7;
  margin: 0 0 20px;
}
.ds-footer__copy {
  font-size: 0.8rem;
  color: rgba(255,255,255,0.25);
  margin: 0;
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
  .ds-hero { flex-direction: column; padding: 90px 20px 48px; }
  .ds-hero__illustration { max-width: 100%; }
  .ds-stats { gap: 16px; }
  .ds-step__connector { width: 100%; height: 2px; }
  .ds-steps { flex-direction: column; align-items: stretch; }
  .ds-step { max-width: 100%; }
}
</style>