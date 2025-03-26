<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ title }}</h3>
        </div>
        <div
            class="card-body"
            :class="{ 'overlay-wrapper': loading, 'p-0': true }"
        >
            <slot></slot>
            <div v-if="loading" class="overlay">
                <i class="fas fa-2x fa-sync-alt fa-spin"></i>
            </div>
        </div>
    </div>
</template>
