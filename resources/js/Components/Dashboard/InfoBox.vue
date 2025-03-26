<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    icon: {
        type: String,
        default: "fas fa-info",
    },
    color: {
        type: String,
        default: "info",
    },
    loading: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="info-box" :class="{ 'overlay-wrapper': loading }">
        <span class="info-box-icon" :class="`bg-${color}`">
            <i :class="icon"></i>
        </span>
        <div class="info-box-content">
            <span class="info-box-text">{{ title }}</span>
            <span class="info-box-number">{{ value }}</span>
        </div>
        <div v-if="loading" class="overlay">
            <i class="fas fa-2x fa-sync-alt fa-spin"></i>
        </div>
    </div>
</template>
