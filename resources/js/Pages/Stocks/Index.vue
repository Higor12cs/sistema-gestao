<!-- resources/js/Pages/Stocks/Index.vue -->
<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Pagination from "@/Components/Pagination.vue";
import { ref } from "vue";
import { formatCurrency } from "@/Utils/Formatters.js";

const props = defineProps({
    stocks: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");

const submit = () => {
    router.get(
        route("stocks.index"),
        { search: search.value },
        { preserveState: true }
    );
};
</script>

<template>
    <Head title="Estoque" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Estoque</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Estoque' },
                    ]"
                />
            </div>
            <Link :href="route('kardex.index')" class="btn btn-primary mb-auto">
                <i class="fas fa-dolly"></i>
                &nbsp; Kardex
            </Link>
        </div>

        <div class="card">
            <div class="card-header">Produtos em Estoque</div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Pesquisar"
                        v-model="search"
                        @keyup.enter="submit"
                    />
                    <div class="input-group-append">
                        <button
                            class="btn btn-default"
                            type="button"
                            @click="submit"
                        >
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr class="text-nowrap">
                                <th class="col-1">Código</th>
                                <th class="col-5">Produto</th>
                                <th class="col-1">Saldo em Estoque</th>
                                <th class="col-2">Valor Unitário</th>
                                <th class="col-2">Valor em Estoque</th>
                                <th class="col-1">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="stock in stocks.data" :key="stock.id">
                                <td>
                                    {{
                                        String(
                                            stock.product.sequential_id
                                        ).padStart(6, "0")
                                    }}
                                </td>
                                <td>{{ stock.product.name }}</td>
                                <td>{{ stock.quantity }}</td>
                                <td>
                                    {{ formatCurrency(stock.product.price) }}
                                </td>
                                <td>
                                    {{
                                        formatCurrency(
                                            stock.product.price * stock.quantity
                                        )
                                    }}
                                </td>
                                <td class="text-nowrap">
                                    <Link
                                        :href="
                                            route(
                                                'stocks.adjust',
                                                stock.id
                                            )
                                        "
                                        class="btn btn-sm btn-primary mr-1"
                                    >
                                        Ajustar Estoque
                                    </Link>
                                    <Link
                                        :href="
                                            route('kardex.index', {
                                                product_id: stock.product_id,
                                            })
                                        "
                                        class="btn btn-sm btn-secondary"
                                    >
                                        Ver Movimentações
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="stocks.data.length === 0">
                                <td colspan="6" class="text-center">
                                    Nenhum registro encontrado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="stocks.links" />
            </div>
        </div>
    </AppLayout>
</template>
