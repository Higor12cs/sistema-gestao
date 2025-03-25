<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    routeName: {
        type: String,
        required: true
    },
    iconClass: {
        type: String,
        required: true
    },
    label: {
        type: String,
        required: true
    }
});
</script>

<template>
    <li class="nav-item">
        <Link
            :href="route(routeName)"
            :class="route().current(routeName) ? 'nav-link active' : 'nav-link'"
            preserve-scroll
        >
            <i :class="`nav-icon ${iconClass}`"></i>
            <p class="ml-1">{{ label }}</p>
        </Link>
    </li>
</template>
