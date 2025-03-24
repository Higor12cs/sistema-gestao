<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { ref, onMounted } from "vue";
import Pagination from "@/Components/Pagination.vue";
import DeleteConfirmation from "@/Components/DeleteConfirmation.vue";
import Select2 from "@/Components/Select2.vue";
import InputField from "@/Components/InputField.vue";

const props = defineProps({
    orders: Object,
    filters: Object,
    hasResults: Boolean,
    selectedCustomer: Object,
    selectedSeller: Object,
    selectedCreatedBy: Object,
});

// Obter as datas para os últimos 7 dias
const getLastSevenDays = () => {
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(endDate.getDate() - 7);

    // Formatação para YYYY-MM-DD
    const formatDate = (date) => {
        return date.toISOString().split("T")[0];
    };

    return {
        start: formatDate(startDate),
        end: formatDate(endDate),
    };
};

// Estado para controlar se o card de filtros está colapsado
const isFilterCardCollapsed = ref(false);

// Toggle para o card de filtros
const toggleFilterCard = () => {
    isFilterCardCollapsed.value = !isFilterCardCollapsed.value;
};

// Inicializar datas de filtro com os últimos 7 dias ou valores definidos nos filtros
const defaultDates = getLastSevenDays();

const filterForm = useForm({
    sequential_id: props.filters?.sequential_id || "",
    customer_id: props.filters?.customer_id || "",
    start_date: props.filters?.start_date || defaultDates.start,
    end_date: props.filters?.end_date || defaultDates.end,
    seller_id: props.filters?.seller_id || "",
    created_by: props.filters?.created_by || "",
    status: props.filters?.status || "",
});

const showDeleteModal = ref(false);
const deleteId = ref(null);
const loading = ref(false);

const applyFilters = () => {
    router.get(route("orders.index"), filterForm.data(), {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    // Resetar para os últimos 7 dias, não para vazio
    const defaultDates = getLastSevenDays();
    filterForm.reset();
    filterForm.start_date = defaultDates.start;
    filterForm.end_date = defaultDates.end;

    router.get(
        route("orders.index"),
        { start_date: defaultDates.start, end_date: defaultDates.end },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const confirmDelete = (orderId) => {
    deleteId.value = orderId;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    loading.value = true;
    return route("orders.destroy", deleteId.value);
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    deleteId.value = null;
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("pt-BR", {
        style: "currency",
        currency: "BRL",
    }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("pt-BR");
};

const formatSequentialId = (id) => {
    return String(id).padStart(6, "0");
};

// Aplicar filtros padrão ao carregar a página se não houver filtros definidos
onMounted(() => {
    if (!props.hasResults) {
        applyFilters();
    }
});
</script>

<template>
    <Head title="Pedidos" />
    <AuthenticatedLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Pedidos</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Pedidos' },
                    ]"
                />
            </div>
            <Link
                :href="route('orders.create')"
                class="btn btn-primary mb-auto"
            >
                <i class="fas fa-sm fa-plus"></i>
                &nbsp; Novo Pedido
            </Link>
        </div>

        <div class="card mb-4">
            <div
                class="card-header d-flex justify-content-between align-items-center"
                style="cursor: pointer"
                @click="toggleFilterCard"
            >
                <div>Filtros</div>
                <div>
                    <i
                        :class="
                            isFilterCardCollapsed
                                ? 'fas fa-sm fa-plus'
                                : 'fas fa-sm fa-minus'
                        "
                    ></i>
                </div>
            </div>
            <div class="card-body" v-show="!isFilterCardCollapsed">
                <div class="row">
                    <div class="col-md-3">
                        <InputField
                            id="sequential_id"
                            label="Código"
                            v-model="filterForm.sequential_id"
                            type="text"
                            placeholder="Código do Pedido"
                        />
                    </div>

                    <div class="col-md-6">
                        <Select2
                            label="Cliente"
                            v-model="filterForm.customer_id"
                            :search-url="route('api.customers.search')"
                            value-key="id"
                            label-key="name"
                            placeholder="Pesquisar Cliente"
                            :initial-options="
                                selectedCustomer ? [selectedCustomer] : []
                            "
                        />
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select
                                v-model="filterForm.status"
                                class="form-control"
                            >
                                <option value="">Todos</option>
                                <option value="pending">Pendente</option>
                                <option value="finalized">Finalizado</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-3">
                        <InputField
                            id="start_date"
                            label="Data Inicial"
                            v-model="filterForm.start_date"
                            type="date"
                        />
                    </div>

                    <div class="col-md-3">
                        <InputField
                            id="end_date"
                            label="Data Final"
                            v-model="filterForm.end_date"
                            type="date"
                        />
                    </div>

                    <div class="col-md-6">
                        <Select2
                            label="Vendedor"
                            v-model="filterForm.seller_id"
                            :search-url="route('api.sellers.search')"
                            value-key="id"
                            label-key="name"
                            placeholder="Pesquisar Vendedor"
                            :initial-options="
                                selectedSeller ? [selectedSeller] : []
                            "
                        />
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <Select2
                            label="Criado por"
                            v-model="filterForm.created_by"
                            :search-url="route('api.users.search')"
                            value-key="id"
                            label-key="name"
                            placeholder="Pesquisar Usuário"
                            :initial-options="
                                selectedCreatedBy ? [selectedCreatedBy] : []
                            "
                        />
                    </div>

                    <div
                        class="col-md-6 d-flex justify-content-end mt-auto pb-3"
                    >
                        <button
                            class="btn btn-secondary mr-2"
                            @click="resetFilters"
                        >
                            <i class="fas fa-times"></i>
                            &nbsp; Limpar Filtros
                        </button>

                        <button class="btn btn-primary" @click="applyFilters">
                            <i class="fas fa-search"></i>
                            &nbsp; Filtrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Lista de Pedidos</div>
            <div class="card-body">
                <div
                    v-if="orders.data.length === 0"
                    class="alert alert-warning"
                >
                    Nenhum pedido encontrado com os filtros aplicados.
                </div>

                <div v-else class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th class="col-1">Código</th>
                                <th class="col-5">Cliente</th>
                                <th class="col-2">Data</th>
                                <th class="col-1">Status</th>
                                <th class="col-2">Valor</th>
                                <th class="col-1">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in orders.data" :key="order.id">
                                <td>
                                    {{
                                        formatSequentialId(order.sequential_id)
                                    }}
                                </td>
                                <td>{{ order.customer.first_name }}</td>
                                <td>{{ formatDate(order.issue_date) }}</td>
                                <td>
                                    <span
                                        class="badge"
                                        :class="
                                            order.receivables &&
                                            order.receivables.length
                                                ? 'bg-success'
                                                : 'bg-warning'
                                        "
                                    >
                                        {{
                                            order.receivables &&
                                            order.receivables.length
                                                ? "Finalizado"
                                                : "Pendente"
                                        }}
                                    </span>
                                </td>
                                <td>{{ formatCurrency(order.total_price) }}</td>
                                <td class="text-nowrap">
                                    <Link
                                        :href="
                                            route(
                                                'orders.show',
                                                order.sequential_id
                                            )
                                        "
                                        class="btn btn-sm btn-secondary mr-1"
                                    >
                                        Visualizar
                                    </Link>
                                    <Link
                                        v-if="
                                            !(
                                                order.receivables &&
                                                order.receivables.length
                                            )
                                        "
                                        :href="
                                            route(
                                                'orders.edit',
                                                order.sequential_id
                                            )
                                        "
                                        class="btn btn-sm btn-secondary mr-1"
                                    >
                                        Editar
                                    </Link>
                                    <Link
                                        v-if="
                                            !(
                                                order.receivables &&
                                                order.receivables.length
                                            )
                                        "
                                        :href="
                                            route(
                                                'orders.create-receivables',
                                                order.sequential_id
                                            )
                                        "
                                        class="btn btn-sm btn-primary mr-1"
                                    >
                                        Finalizar
                                    </Link>
                                    <button
                                        @click="confirmDelete(order.id)"
                                        class="btn btn-sm btn-danger"
                                    >
                                        Excluir
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="orders.links" />
            </div>
        </div>

        <DeleteConfirmation
            v-if="showDeleteModal"
            :visible="showDeleteModal"
            :loading="loading"
            title="Confirmar Exclusão"
            message="Você tem certeza que deseja excluir este pedido?"
            warning="Esta ação não pode ser desfeita."
            delete-route-method="delete"
            :delete-route="handleDelete"
            @cancel="cancelDelete"
            success-redirect="orders.index"
            success-message="Pedido excluído com sucesso!"
        />
    </AuthenticatedLayout>
</template>
