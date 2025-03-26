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
    description: {
        type: String,
        default: "",
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
    <div class="small-box" :class="`bg-${color}`">
        <div class="inner">
            <h3>{{ value }}</h3>
            <p>{{ title }}</p>
        </div>
        <div class="icon">
            <i :class="icon"></i>
        </div>
        <div class="small-box-footer">
            {{ description }} <i class="fas fa-arrow-circle-right"></i>
        </div>
        <div v-if="loading" class="overlay">
            <i class="fas fa-2x fa-sync-alt fa-spin"></i>
        </div>
    </div>
</template>

<style scoped>
.small-box {
    position: relative;
}
.overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.25rem;
}
</style>
