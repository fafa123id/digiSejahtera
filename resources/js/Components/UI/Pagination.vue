<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  pagination: {
    type: Object,
    required: true,
  },
})
</script>

<template>
  <div
    v-if="pagination.links?.length > 3"
    class="flex flex-wrap items-center justify-between gap-3 border-t border-blue-50 px-5 py-4"
  >
    <p class="text-xs font-medium text-slate-400">
      Menampilkan {{ pagination.from }}–{{ pagination.to }}
      dari {{ pagination.total }} data
    </p>

    <div class="flex flex-wrap gap-1">
      <Link
        v-for="link in pagination.links"
        :key="link.label"
        :href="link.url ?? '#'"
        preserve-scroll
        class="rounded-lg px-3 py-2 text-xs font-bold transition"
        :class="[
          link.active
            ? 'bg-[#1a6fbd] text-white'
            : 'bg-blue-50 text-[#1a6fbd] hover:bg-blue-100',
          !link.url ? 'pointer-events-none opacity-40' : '',
        ]"
        v-html="link.label"
      />
    </div>
  </div>
</template>