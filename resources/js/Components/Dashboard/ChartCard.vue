<script setup>
import { onMounted, ref, watch, nextTick } from "vue";
import Chart from "chart.js/auto";

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    type: {
        type: String,
        default: "line",
    },
    data: {
        type: Object,
        required: true,
    },
    options: {
        type: Object,
        default: () => ({}),
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const chartCanvas = ref(null);
let chartInstance = null;

onMounted(() => {
    renderChart();
});

const renderChart = () => {
    if (chartInstance) {
        chartInstance.destroy();
    }

    if (chartCanvas.value) {
        chartInstance = new Chart(chartCanvas.value, {
            type: props.type,
            data: props.data,
            options: props.options,
        });
    }
};

watch(
    () => props.data,
    () => {
        nextTick(() => {
            renderChart();
        });
    },
    { deep: true }
);
</script>

<template>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ title }}</h3>
        </div>
        <div class="card-body" :class="{ 'overlay-wrapper': loading }">
            <canvas ref="chartCanvas"></canvas>
            <div v-if="loading" class="overlay">
                <i class="fas fa-2x fa-sync-alt fa-spin"></i>
            </div>
        </div>
    </div>
</template>
