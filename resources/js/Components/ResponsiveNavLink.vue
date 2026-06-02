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
    'ds-responsive-nav-link': true,
    'ds-responsive-nav-link--active': props.active,
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
.ds-responsive-nav-link {
  display: block;
  width: 100%;
  padding: 12px 16px;
  border-left: 4px solid transparent;
  font-size: 0.95rem;
  font-weight: 600;
  text-align: left;
  color: var(--gray-600);
  text-decoration: none;
  transition: all 0.2s ease;
  border-radius: 0 8px 8px 0;
}

.ds-responsive-nav-link:hover {
  color: var(--gray-800);
  background: rgba(26, 111, 189, 0.05);
  border-left-color: var(--blue-light);
}

.ds-responsive-nav-link--active {
  color: var(--blue);
  background: rgba(26, 111, 189, 0.1);
  border-left-color: var(--blue);
  font-weight: 700;
}

.ds-responsive-nav-link--active:hover {
  color: var(--blue-dark);
  background: rgba(26, 111, 189, 0.15);
  border-left-color: var(--blue-dark);
}
</style>
