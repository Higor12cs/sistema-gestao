<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Pagination from "@/Components/Pagination.vue";
import Select2 from "@/Components/Select2.vue";
import { ref } from "vue";
import { formatDate } from "@/Utils/Formatters";

const props = defineProps({
    product: Object,
    movements: Object,
    filters: Object,
});

const productId = ref(props.filters.product_id || null);
const startDate = ref(props.filters.start_date || null);
const endDate = ref(props.filters.end_date || null);

const getTypeLabel = (type) => {
    switch (type) {
        case "in":
            return "Entrada";
        case "out":
            return "Saída";
        case "adjustment":
            return "Ajuste";
        default:
            return type;
    }
};

const getTypeClass = (type) => {
    switch (type) {
        case "in":
            return "bg-success";
        case "out":
            return "bg-danger";
        case "adjustment":
            return "bg-warning";
        default:
            return "bg-secondary";
    }
};

const getSourceLabel = (sourceType) => {
    switch (sourceType) {
        case "purchase":
            return "Compra";
        case "order":
            return "Pedido";
        case "adjustment":
            return "Ajuste Manual";
        case "initial":
            return "Estoque Inicial";
        default:
            return sourceType;
    }
};

const submit = () => {
    router.get(
        route("kardex.index"),
        {
            product_id: productId.value,
            start_date: startDate.value,
            end_date: endDate.value,
        },
        { preserveState: true }
    );
};

const resetFilters = () => {
    productId.value = null;
    startDate.value = null;
    endDate.value = null;
    router.get(route("kardex.index"));
};
</script>

<template>
    <Head title="Kardex" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Kardex - Movimentações de Estoque</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Estoque', routeName: 'stocks.index' },
                        { label: 'Kardex' },
                    ]"
                />
            </div>
            <Link
                :href="route('stocks.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>

        <div class="card mb-4">
            <div class="card-header">Filtros</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Produto</label>
                            <Select2
                                v-model="productId"
                                :search-url="route('api.products.search')"
                                placeholder="Selecione um Produto"
                            />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Data Inicial</label>
                            <input
                                type="date"
                                class="form-control"
                                v-model="startDate"
                            />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Data Final</label>
                            <input
                                type="date"
                                class="form-control"
                                v-model="endDate"
                            />
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button
                        type="button"
                        class="btn btn-secondary mr-2"
                        @click="resetFilters"
                    >
                        <i class="fas fa-times"></i>
                        &nbsp; Limpar Filtros
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        @click="submit"
                    >
                        <i class="fas fa-search"></i>
                        &nbsp; Filtrar
                    </button>
                </div>
            </div>
        </div>

        <div v-if="product && movements" class="card">
            <div class="card-header">
                Movimentações do Produto:
                <strong>
                    {{ product.name }}
                    (Código:
                    {{ String(product.sequential_id).padStart(6, "0") }})
                </strong>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th>Origem</th>
                                <th>Saldo Anterior</th>
                                <th>Movimentação</th>
                                <th>Novo Saldo</th>
                                <th>Observações</th>
                                <th>Usuário</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="movement in movements.data"
                                :key="movement.id"
                            >
                                <td>{{ formatDate(movement.created_at) }}</td>
                                <td>
                                    <span
                                        class="badge"
                                        :class="getTypeClass(movement.type)"
                                    >
                                        {{ getTypeLabel(movement.type) }}
                                    </span>
                                </td>
                                <td>
                                    {{ getSourceLabel(movement.source_type) }}
                                </td>
                                <td>{{ movement.previous_quantity }}</td>
                                <td>
                                    {{
                                        movement.type === "out"
                                            ? `-${movement.quantity}`
                                            : `+${movement.quantity}`
                                    }}
                                </td>
                                <td>{{ movement.new_quantity }}</td>
                                <td>{{ movement.notes }}</td>
                                <td>{{ movement.created_by.name }}</td>
                            </tr>
                            <tr v-if="movements.data.length === 0">
                                <td colspan="8" class="text-center">
                                    Nenhum registro encontrado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="movements.links" />
            </div>
        </div>

        <div v-else-if="productId" class="alert alert-info">
            Carregando movimentações...
        </div>

        <div v-else class="alert alert-info">
            Por favor, selecione um produto para visualizar suas movimentações.
        </div>
    </AppLayout>
</template>
