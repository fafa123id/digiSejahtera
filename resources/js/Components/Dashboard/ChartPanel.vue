<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },

  description: {
    type: String,
    default: '',
  },
})
</script>

<template>
  <section class="rounded-3xl border border-blue-100 bg-white p-5 shadow-[0_8px_28px_rgba(26,111,189,0.08)]">
    <header class="flex flex-wrap items-start justify-between gap-3">
      <div>
        <h3 class="text-base font-black text-slate-800">
          {{ title }}
        </h3>

        <p class="mt-1 text-xs leading-5 text-slate-400">
          {{ description }}
        </p>
      </div>

      <slot name="action" />
    </header>

    <div class="mt-5">
      <slot />
    </div>
  </section>
</template>