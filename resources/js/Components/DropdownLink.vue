<script setup>
import { Link } from "@inertiajs/vue3";
import { computed } from 'vue';
const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    method: {
        type: String,
        default: "get",
    },
    as: {
        type: String,
        default: "a",
    },
    active: {
        type: Boolean,
    },
});
const classes = computed(() => ({
    'ds-nav-link--active': props.active,
    'ds-dropdown-link': true,
}));
</script>

<template>
    <Link :href="href" :method="method" :as="as" :class="classes">
        <slot />
    </Link>
</template>

<style scoped>
.ds-dropdown-link {
    display: block;
    width: 100%;
    padding: 10px 16px;
    text-align: left;
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--gray-700);
    text-decoration: none;
    transition: all 0.15s ease;
    border-left: 3px solid transparent;
    margin: 4px 0;
}

.ds-dropdown-link:hover {
    background: rgba(26, 111, 189, 0.08);
    color: var(--blue);
    border-left-color: var(--blue);
    padding-left: 18px;
}

.ds-dropdown-link:focus {
    outline: none;
    background: rgba(26, 111, 189, 0.12);
    color: var(--blue-dark);
    border-left-color: var(--blue-dark);
}
.ds-nav-link--active {
  color: var(--blue);
  background: rgba(26, 111, 189, 0.1);
  border-left-color: var(--blue);
  font-weight: 700;
}

.ds-nav-link--active:hover {
  color: var(--blue-dark);
  background: rgba(26, 111, 189, 0.15);
  border-left-color: var(--blue-dark);
}
</style>
