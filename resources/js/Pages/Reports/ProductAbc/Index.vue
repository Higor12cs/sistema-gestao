<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import DateRangePicker from "@/Components/DateRangePicker.vue";
import Select2 from "@/Components/Select2.vue";
import { ref, watch } from "vue";

const form = useForm({
    analysis_type: "value",
    brand_id: null,
    section_id: null,
    group_id: null,
    startDate: new Date(new Date().getFullYear(), new Date().getMonth() - 2, 1)
        .toISOString()
        .split("T")[0],
    endDate: new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0)
        .toISOString()
        .split("T")[0],
});

const loading = ref(false);

// Reset group if section changes
watch(
    () => form.section_id,
    (newValue) => {
        form.group_id = null;
    }
);

const handleSubmit = () => {
    loading.value = true;
    window.open(
        route("reports.product-abc.generate", {
            analysis_type: form.analysis_type,
            brand_id: form.brand_id,
            section_id: form.section_id,
            group_id: form.group_id,
            start_date: form.startDate,
            end_date: form.endDate,
        }),
        "_blank"
    );
    loading.value = false;
};
</script>

<template>
    <Head title="Relatório Curva ABC - Produtos" />
    <AuthenticatedLayout>
        <div class="d-flex justify-content-between mb-3">
            <h4>Relatório Curva ABC - Produtos</h4>
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
                                        Por Quantidade (Itens)
                                    </option>
                                </select>
                                <small class="form-text text-muted">
                                    Selecione se deseja classificar produtos por
                                    valor total em vendas ou por quantidade
                                    vendida.
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

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="brand">Marca</label>
                                <Select2
                                    id="brand"
                                    v-model="form.brand_id"
                                    placeholder="Todas as Marcas"
                                    :searchUrl="route('api.brands.search')"
                                />
                                <small class="form-text text-muted">
                                    Opcional. Filtrar produtos por marca.
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="section">Seção</label>
                                <Select2
                                    id="section"
                                    v-model="form.section_id"
                                    placeholder="Todas as Seções"
                                    :searchUrl="route('api.sections.search')"
                                />
                                <small class="form-text text-muted">
                                    Opcional. Filtrar produtos por seção.
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="group">Grupo</label>
                                <Select2
                                    id="group"
                                    v-model="form.group_id"
                                    placeholder="Todos os Grupos"
                                    :searchUrl="
                                        form.section_id
                                            ? `${route(
                                                  'api.groups.search'
                                              )}?section_id=${form.section_id}`
                                            : route('api.groups.search')
                                    "
                                />
                                <small class="form-text text-muted">
                                    Opcional. Filtrar produtos por grupo.
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
                    identificar quais produtos são responsáveis pela maior parte
                    do faturamento ou volume de vendas. Os produtos são
                    classificados em três categorias:
                </p>
                <ul>
                    <li>
                        <strong>Classe A:</strong> Aproximadamente 20% dos
                        produtos que representam cerca de 80% do faturamento
                        total. São os produtos mais rentáveis e estratégicos.
                    </li>
                    <li>
                        <strong>Classe B:</strong> Aproximadamente 30% dos
                        produtos que representam cerca de 15% do faturamento
                        total. São produtos importantes, mas com impacto menor
                        que os da Classe A.
                    </li>
                    <li>
                        <strong>Classe C:</strong> Aproximadamente 50% dos
                        produtos que representam cerca de 5% do faturamento
                        total. São produtos que, individualmente, têm menor
                        impacto no negócio.
                    </li>
                </ul>
                <p>
                    Utilize esta análise para otimizar seu estoque, priorizar
                    produtos, e desenvolver estratégias específicas para cada
                    categoria.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
