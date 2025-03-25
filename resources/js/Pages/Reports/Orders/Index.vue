<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import DateRangePicker from "@/Components/DateRangePicker.vue";
import { ref } from "vue";

const startDate = ref('2023-01-01');
const endDate = ref('2023-01-31');

const handleDateChange = ({ startDate, endDate }) => {
  console.log('Datas alteradas:', startDate, endDate);
};
</script>

<template>
    <Head title="Relatórios de Pedidos" />
    <AuthenticatedLayout>
        <div class="d-flex justify-content-between mb-3">
            <h4>Relatórios de Pedidos</h4>
        </div>
        <div class="card">
            <div class="card-header">Relatórios de Pedidos</div>
            <div class="card-body">
                <DateRangePicker
                    v-model:startDate="startDate"
                    v-model:endDate="endDate"
                    @change="handleDateChange"
                    placeholder="Selecione o Período"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
