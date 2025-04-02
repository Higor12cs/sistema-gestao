<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { ref, onMounted } from "vue";
import Pagination from "@/Components/Pagination.vue";
import DeleteConfirmation from "@/Components/DeleteConfirmation.vue";
import FilterModal from "@/Pages/Orders/FilterModal.vue";

const props = defineProps({
    orders: Object,
    filters: Object,
    hasResults: Boolean,
    selectedCustomer: Object,
    selectedSeller: Object,
    selectedCreatedBy: Object,
});

const getLastSevenDays = () => {
    const endDate = new Date();
    const startDate = new Date(endDate.getFullYear(), endDate.getMonth(), 1);

    const formatDate = (date) => {
        return date.toISOString().split("T")[0];
    };

    return {
        start: formatDate(startDate),
        end: formatDate(endDate),
    };
};

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

const showFilterModal = ref(false);
const showDeleteModal = ref(false);
const deleteId = ref(null);
const loading = ref(false);

const toggleFilterModal = () => {
    showFilterModal.value = !showFilterModal.value;
};

const applyFilters = (formData) => {
    filterForm.sequential_id = formData.sequential_id;
    filterForm.customer_id = formData.customer_id;
    filterForm.start_date = formData.start_date;
    filterForm.end_date = formData.end_date;
    filterForm.seller_id = formData.seller_id;
    filterForm.created_by = formData.created_by;
    filterForm.status = formData.status;

    router.get(route("orders.index"), filterForm.data(), {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
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
    if (dateString.includes('T')) {
        dateString = dateString.split('T')[0];
    }

    const [year, month, day] = dateString.split('-');

    return `${day}/${month}/${year}`;
};

const formatSequentialId = (id) => {
    return String(id).padStart(6, "0");
};

onMounted(() => {
    if (!props.hasResults) {
        applyFilters(filterForm);
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
            <div>
                <button
                    @click="toggleFilterModal"
                    class="btn btn-secondary mr-2"
                >
                    <i class="fas fa-filter"></i>
                    &nbsp; Filtrar
                </button>
                <Link :href="route('orders.create')" class="btn btn-primary">
                    <i class="fas fa-sm fa-plus"></i>
                    &nbsp; Novo Pedido
                </Link>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Lista de Pedidos</div>
            <div class="card-body">
                <div v-if="orders.data.length === 0" class="alert alert-info">
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
                                <td>
                                    {{ order.customer.name }}
                                </td>
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

        <FilterModal
            v-if="showFilterModal"
            :visible="showFilterModal"
            :filters="filterForm"
            :selectedCustomer="selectedCustomer"
            :selectedSeller="selectedSeller"
            :selectedCreatedBy="selectedCreatedBy"
            @cancel="showFilterModal = false"
            @filter="applyFilters"
            @reset="resetFilters"
        />

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
