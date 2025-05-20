<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import DateRangePicker from "@/Components/DateRangePicker.vue";
import Select2 from "@/Components/Select2.vue";
import { ref } from "vue";

const form = useForm({
    supplier_id: null,
    chart_account_id: null,
    created_by: null,
    date_type: "issue_date",
    status: "all",
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
        route("reports.payables.synthetics.print", {
            supplier_id: form.supplier_id,
            chart_account_id: form.chart_account_id,
            created_by: form.created_by,
            date_type: form.date_type,
            status: form.status,
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
    <Head title="Relatório Sintético de Pagáveis" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <h4>Relatório Sintético de Pagáveis</h4>
        </div>
        <div class="card">
            <div class="card-header">Filtros</div>
            <div class="card-body">
                <form @submit.prevent="handleSubmit">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="supplier">Fornecedor</label>
                                <Select2
                                    id="supplier"
                                    v-model="form.supplier_id"
                                    :class="{
                                        'is-invalid': form.errors.supplier_id,
                                    }"
                                    placeholder="Pesquisar"
                                    :searchUrl="route('api.suppliers.search')"
                                />
                                <div class="invalid-feedback">
                                    {{ form.errors.supplier_id }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="chart_account"
                                    >Plano de Contas</label
                                >
                                <Select2
                                    id="chart_account"
                                    v-model="form.chart_account_id"
                                    :class="{
                                        'is-invalid':
                                            form.errors.chart_account_id,
                                    }"
                                    placeholder="Pesquisar"
                                    :searchUrl="
                                        route('api.chart-accounts.search')
                                    "
                                />
                                <div class="invalid-feedback">
                                    {{ form.errors.chart_account_id }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date_type">Tipo de Data</label>
                                <select
                                    id="date_type"
                                    v-model="form.date_type"
                                    class="form-control"
                                >
                                    <option value="issue_date">
                                        Data de Emissão
                                    </option>
                                    <option value="due_date">
                                        Data de Vencimento
                                    </option>
                                    <option value="payment_date">
                                        Data de Pagamento
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="form-control"
                                >
                                    <option value="all">Todos</option>
                                    <option value="open">Em Aberto</option>
                                    <option value="paid">Pagos</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
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
                        <div class="col-md-3">
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

                    <div class="row mt-3">
                        <div class="col-md-12">
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
