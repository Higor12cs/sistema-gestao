<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import DateRangePicker from "@/Components/DateRangePicker.vue";
import { ref } from "vue";

const form = useForm({
    analysis_type: "value",
    startDate: new Date(new Date().getFullYear(), new Date().getMonth() - 2, 1)
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
        route("reports.customer-abc.generate", {
            analysis_type: form.analysis_type,
            start_date: form.startDate,
            end_date: form.endDate,
        }),
        "_blank"
    );
    loading.value = false;
};
</script>

<template>
    <Head title="Relatório Curva ABC - Clientes" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <h4>Relatório Curva ABC - Clientes</h4>
        </div>
        <div class="card">
            <div class="card-header">Filtros</div>
            <div class="card-body">
                <form @submit.prevent="handleSubmit">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="analysis_type"
                                    >Tipo de Análise</label
                                >
                                <select
                                    id="analysis_type"
                                    v-model="form.analysis_type"
                                    class="form-control"
                                >
                                    <option value="value">
                                        Por Valor (R$)
                                    </option>
                                    <option value="quantity">
                                        Por Quantidade (Pedidos)
                                    </option>
                                </select>
                                <small class="form-text text-muted">
                                    Selecione se deseja classificar clientes por
                                    valor total em vendas ou por quantidade de
                                    pedidos realizados.
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <DateRangePicker
                                    v-model:startDate="form.startDate"
                                    v-model:endDate="form.endDate"
                                    placeholder="Período de Análise"
                                    required
                                />
                                <small class="form-text text-muted">
                                    Período considerado para análise das vendas.
                                </small>
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
                                <i class="fas fa-chart-pie"></i>
                                &nbsp; Gerar Curva ABC
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">Sobre a Curva ABC</div>
            <div class="card-body">
                <p>
                    A análise de Curva ABC é uma ferramenta que permite
                    identificar quais clientes são responsáveis pela maior parte
                    do faturamento ou volume de pedidos. Os clientes são
                    classificados em três categorias:
                </p>
                <ul>
                    <li>
                        <strong>Classe A:</strong> Aproximadamente 20% dos
                        clientes que representam cerca de 80% do faturamento
                        total. São os clientes mais valiosos para o negócio.
                    </li>
                    <li>
                        <strong>Classe B:</strong> Aproximadamente 30% dos
                        clientes que representam cerca de 15% do faturamento
                        total. São clientes importantes, mas com impacto menor
                        que os da Classe A.
                    </li>
                    <li>
                        <strong>Classe C:</strong> Aproximadamente 50% dos
                        clientes que representam cerca de 5% do faturamento
                        total. São clientes que, individualmente, têm menor
                        impacto no negócio.
                    </li>
                </ul>
                <p>
                    Utilize esta análise para identificar seus clientes mais
                    valiosos e direcionar estratégias específicas para cada
                    categoria.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
