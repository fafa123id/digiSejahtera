<script setup>
import { computed } from 'vue'

const props = defineProps({
  pagination: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits([
  'change',
])

const visiblePages = computed(() => {
  const current = props.pagination.current_page
  const last = props.pagination.last_page
  const pages = []

  for (let page = 1; page <= last; page++) {
    if (
      page === 1
      || page === last
      || Math.abs(page - current) <= 2
    ) {
      pages.push(page)
    }
  }

  return pages.reduce((result, page, index) => {
    const previous = pages[index - 1]

    if (previous && page - previous > 1) {
      result.push(`ellipsis-${previous}-${page}`)
    }

    result.push(page)

    return result
  }, [])
})

const changePage = (page) => {
  if (
    typeof page !== 'number'
    || page < 1
    || page > props.pagination.last_page
    || page === props.pagination.current_page
  ) {
    return
  }

  emit('change', page)
}
</script>

<template>
  <div
    v-if="pagination.last_page > 1"
    class="flex flex-col gap-3 border-t border-blue-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
  >
    <p class="text-xs font-medium text-slate-400">
      Menampilkan {{ pagination.from }}–{{ pagination.to }}
      dari {{ pagination.total }} data
    </p>

    <div class="flex flex-wrap items-center gap-1">
      <button
        type="button"
        :disabled="pagination.current_page === 1"
        class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-bold text-[#1a6fbd] transition hover:bg-blue-100 disabled:cursor-not-allowed disabled:opacity-40"
        @click="changePage(pagination.current_page - 1)"
      >
        ‹
      </button>

      <template
        v-for="page in visiblePages"
        :key="page"
      >
        <span
          v-if="typeof page !== 'number'"
          class="px-2 py-2 text-xs font-bold text-slate-400"
        >
          …
        </span>

        <button
          v-else
          type="button"
          class="rounded-lg px-3 py-2 text-xs font-bold transition"
          :class="
            page === pagination.current_page
              ? 'bg-[#1a6fbd] text-white'
              : 'bg-blue-50 text-[#1a6fbd] hover:bg-blue-100'
          "
          @click="changePage(page)"
        >
          {{ page }}
        </button>
      </template>

      <button
        type="button"
        :disabled="pagination.current_page === pagination.last_page"
        class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-bold text-[#1a6fbd] transition hover:bg-blue-100 disabled:cursor-not-allowed disabled:opacity-40"
        @click="changePage(pagination.current_page + 1)"
      >
        ›
      </button>
    </div>
  </div>
</template>