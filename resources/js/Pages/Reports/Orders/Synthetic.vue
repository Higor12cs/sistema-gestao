<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import DateRangePicker from "@/Components/DateRangePicker.vue";
import Select2 from "@/Components/Select2.vue";
import { ref } from "vue";

const form = useForm({
    customer_id: null,
    seller_id: null,
    created_by: null,
    group_by: "day",
    startDate: new Date(new Date().getFullYear(), new Date().getMonth(), 1)
        .toISOString()
        .split("T")[0],
    endDate: new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0)
        .toISOString()
        .split("T")[0],
});

const loading = ref(false);

const handleSubmit = () => {
    loading.value = true;
    window.open(
        route("reports.orders.synthetics.print", {
            customer_id: form.customer_id,
            seller_id: form.seller_id,
            created_by: form.created_by,
            group_by: form.group_by,
            start_date: form.startDate,
            end_date: form.endDate,
        }),
        "_blank"
    );
    loading.value = false;
};
</script>

<template>
    <Head title="Relatório Sintético de Pedidos" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <h4>Relatório Sintético de Pedidos</h4>
        </div>
        <div class="card">
            <div class="card-header">Filtros</div>
            <div class="card-body">
                <form @submit.prevent="handleSubmit">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="customer">Cliente</label>
                                <Select2
                                    id="customer"
                                    v-model="form.customer_id"
                                    :class="{
                                        'is-invalid': form.errors.customer_id,
                                    }"
                                    placeholder="Pesquisar"
                                    :searchUrl="route('api.customers.search')"
                                />
                                <div class="invalid-feedback">
                                    {{ form.errors.customer_id }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <DateRangePicker
                                    v-model:startDate="form.startDate"
                                    v-model:endDate="form.endDate"
                                    placeholder="Selecione o Período"
                                    required
                                />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="seller">Vendedor</label>
                                <Select2
                                    id="seller"
                                    v-model="form.seller_id"
                                    :class="{
                                        'is-invalid': form.errors.seller_id,
                                    }"
                                    placeholder="Pesquisar"
                                    :searchUrl="route('api.sellers.search')"
                                />
                                <div class="invalid-feedback">
                                    {{ form.errors.seller_id }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="created_by">Criado Por</label>
                                <Select2
                                    id="created_by"
                                    v-model="form.created_by"
                                    :class="{
                                        'is-invalid': form.errors.created_by,
                                    }"
                                    placeholder="Pesquisar"
                                    :searchUrl="route('api.users.search')"
                                />
                                <div class="invalid-feedback">
                                    {{ form.errors.created_by }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="group_by">
                                    Agrupar Por
                                    <span class="text-danger">*</span>
                                </label>
                                <select
                                    id="group_by"
                                    v-model="form.group_by"
                                    class="form-control"
                                >
                                    <option value="day">Dia</option>
                                    <option value="week">Semana</option>
                                    <option value="month">Mês</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button
                            type="submit"
                            class="btn btn-primary"
                            :disabled="loading"
                        >
                            <span
                                v-if="loading"
                                class="spinner-border spinner-border-sm mr-2"
                                role="status"
                                aria-hidden="true"
                            ></span>
                            <span v-if="loading">Gerando...</span>
                            <span v-else>
                                <i class="fas fa-print"></i>
                                &nbsp; Gerar Relatório
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
