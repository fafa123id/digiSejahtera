<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
    method: {
        type: String,
        default: 'get',
    },
    as: {
        type: String,
        default: 'a',
    },
});

const classes = computed(() => ({
    'ds-nav-link': true,
    'ds-nav-link--active': props.active,
}));
</script>

<template>
    <Link 
        :href="href" 
        :method="method"
        :as="as"
        :class="classes"
    >
        <slot />
    </Link>
</template>

<style scoped>
.ds-nav-link {
  display: inline-flex;
  align-items: center;
  padding: 6px 12px;
  border-bottom: 3px solid transparent;
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--gray-500);
  text-decoration: none;
  transition: all 0.2s ease;
  position: relative;
}

.ds-nav-link:hover {
  color: var(--gray-700);
  border-bottom-color: var(--gray-300);
}

.ds-nav-link--active {
  color: var(--blue);
  border-bottom-color: var(--blue);
  font-weight: 700;
}

.ds-nav-link--active:hover {
  color: var(--blue-dark);
  border-bottom-color: var(--blue-dark);
}
</style>
