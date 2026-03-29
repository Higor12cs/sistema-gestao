<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { ref, onMounted } from "vue";
import Pagination from "@/Components/Pagination.vue";
import DeleteConfirmation from "@/Components/DeleteConfirmation.vue";
import FilterModal from "@/Pages/Purchases/FilterModal.vue";
import {
    formatCurrency,
    formatDate,
    formatSequentialId,
} from "@/Utils/Formatters.js";

const props = defineProps({
    purchases: Object,
    filters: Object,
    hasResults: Boolean,
    selectedSupplier: Object,
    selectedCreatedBy: Object,
});

const getLastSevenDays = () => {
    const endDate = new Date();
    const startDate = new Date(endDate.getFullYear(), endDate.getMonth(), 1);

    return {
        start: formatIsoDate(startDate),
        end: formatIsoDate(endDate),
    };
};

const defaultDates = getLastSevenDays();

const filterForm = useForm({
    id: props.filters?.id || "",
    supplier_id: props.filters?.supplier_id || "",
    start_date: props.filters?.start_date || defaultDates.start,
    end_date: props.filters?.end_date || defaultDates.end,
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
    filterForm.id = formData.id;
    filterForm.supplier_id = formData.supplier_id;
    filterForm.start_date = formData.start_date;
    filterForm.end_date = formData.end_date;
    filterForm.created_by = formData.created_by;
    filterForm.status = formData.status;

    router.get(route("purchases.index"), filterForm.data(), {
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
        route("purchases.index"),
        { start_date: defaultDates.start, end_date: defaultDates.end },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const confirmDelete = (purchaseId) => {
    deleteId.value = purchaseId;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    loading.value = true;
    return route("purchases.destroy", deleteId.value);
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    deleteId.value = null;
};

onMounted(() => {
    if (!props.hasResults) {
        applyFilters(filterForm);
    }
});
</script>

<template>
    <Head title="Compras" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Compras</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Compras' },
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
                <Link :href="route('purchases.create')" class="btn btn-primary">
                    <i class="fas fa-sm fa-plus"></i>
                    &nbsp; Nova Compra
                </Link>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Lista de Compras</div>
            <div class="card-body">
                <div
                    v-if="purchases.data.length === 0"
                    class="alert alert-info"
                >
                    Nenhuma compra encontrada com os filtros aplicados.
                </div>

                <div v-else class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th class="col-1">Código</th>
                                <th class="col-5">Fornecedor</th>
                                <th class="col-2">Data</th>
                                <th class="col-1">Status</th>
                                <th class="col-2">Valor</th>
                                <th class="col-1">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="purchase in purchases.data"
                                :key="purchase.id"
                            >
                                <td>
                                    {{ formatSequentialId(purchase.id) }}
                                </td>
                                <td>{{ purchase.supplier.first_name }}</td>
                                <td>{{ formatDate(purchase.issue_date) }}</td>
                                <td>
                                    <span
                                        class="badge"
                                        :class="
                                            purchase.payables &&
                                            purchase.payables.length
                                                ? 'bg-success'
                                                : 'bg-warning'
                                        "
                                    >
                                        {{
                                            purchase.payables &&
                                            purchase.payables.length
                                                ? "Finalizado"
                                                : "Pendente"
                                        }}
                                    </span>
                                </td>
                                <td>
                                    {{ formatCurrency(purchase.total_cost) }}
                                </td>
                                <td class="text-nowrap">
                                    <Link
                                        :href="
                                            route('purchases.show', purchase.id)
                                        "
                                        class="btn btn-sm btn-secondary mr-1"
                                    >
                                        Visualizar
                                    </Link>
                                    <Link
                                        v-if="
                                            !(
                                                purchase.payables &&
                                                purchase.payables.length
                                            )
                                        "
                                        :href="
                                            route('purchases.edit', purchase.id)
                                        "
                                        class="btn btn-sm btn-secondary mr-1"
                                    >
                                        Editar
                                    </Link>
                                    <Link
                                        v-if="
                                            !(
                                                purchase.payables &&
                                                purchase.payables.length
                                            )
                                        "
                                        :href="
                                            route(
                                                'purchases.create-payables',
                                                purchase.id,
                                            )
                                        "
                                        class="btn btn-sm btn-primary mr-1"
                                    >
                                        Finalizar
                                    </Link>
                                    <button
                                        @click="confirmDelete(purchase.id)"
                                        class="btn btn-sm btn-danger"
                                    >
                                        Excluir
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="purchases.links" />
            </div>
        </div>

        <FilterModal
            v-if="showFilterModal"
            :visible="showFilterModal"
            :filters="filterForm"
            :selectedSupplier="selectedSupplier"
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
            message="Você tem certeza que deseja excluir esta compra?"
            warning="Esta ação não pode ser desfeita."
            delete-route-method="delete"
            :delete-route="handleDelete"
            @cancel="cancelDelete"
            success-redirect="purchases.index"
            success-message="Compra excluída com sucesso!"
        />
    </AppLayout>
</template>
