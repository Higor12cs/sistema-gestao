<script setup>
import { ref, computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, useForm, router, Link } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";
import DeleteConfirmation from "@/Components/DeleteConfirmation.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Select2 from "@/Components/Select2.vue";
import InputField from "@/Components/InputField.vue";
import {
    formatCurrency,
    formatDate,
    formatSequentialId,
} from "@/Utils/Formatters.js";

const props = defineProps({
    receivables: Object,
    filters: Object,
    hasResults: Boolean,
    selectedCustomer: Object,
});

const filterForm = useForm({
    start_date: props.filters?.start_date || "",
    end_date: props.filters?.end_date || "",
    date_type: props.filters?.date_type || "due",
    customer_id: props.filters?.customer_id || "",
    status: props.filters?.status || "",
});

const loading = ref(false);
const showDeleteModal = ref(false);
const deleteIds = ref([]);
const selectedReceivables = ref([]);
const selectedCustomerId = ref(null);

const applyFilters = () => {
    selectedReceivables.value = [];
    selectedCustomerId.value = null;

    router.get(route("receivables.index"), filterForm.data(), {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    filterForm.reset();
    selectedReceivables.value = [];
    selectedCustomerId.value = null;

    router.get(
        route("receivables.index"),
        {},
        {
            preserveState: true,
            replace: true,
        }
    );
};

const toggleReceivableSelection = (receivable) => {
    const index = selectedReceivables.value.findIndex(
        (r) => r.id === receivable.id
    );

    if (index === -1) {
        if (
            selectedReceivables.value.length > 0 &&
            selectedReceivables.value[0].customer_id !== receivable.customer_id
        ) {
            alert("Só é possível selecionar recebíveis do mesmo cliente.");
            return;
        }

        selectedReceivables.value.push(receivable);
        if (!selectedCustomerId.value) {
            selectedCustomerId.value = receivable.customer_id;
        }
    } else {
        selectedReceivables.value.splice(index, 1);
        if (selectedReceivables.value.length === 0) {
            selectedCustomerId.value = null;
        }
    }
};

const isSelected = (receivable) => {
    return selectedReceivables.value.some((r) => r.id === receivable.id);
};

const openPaymentPage = () => {
    if (selectedReceivables.value.length === 0) {
        alert("Por favor, selecione pelo menos um recebível para pagamento.");
        return;
    }

    const ids = selectedReceivables.value.map((r) => r.id).join(",");
    window.location.href = route("receivables.payments.create", { ids });
};

const confirmDelete = () => {
    if (selectedReceivables.value.length === 0) {
        alert("Por favor, selecione pelo menos um recebível para exclusão.");
        return;
    }

    deleteIds.value = selectedReceivables.value.map((r) => r.id).join(",");
    showDeleteModal.value = true;
};

const handleDelete = () => {
    loading.value = true;
    return route("receivables.destroy", { ids: deleteIds.value });
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    deleteIds.value = null;
};

const getStatusClass = (status) => {
    switch (status) {
        case "paid":
            return "badge-primary";
        case "partial":
            return "badge-secondary";
        case "pending":
            return "badge-warning";
        default:
            return "badge-secondary";
    }
};

const getStatusText = (status) => {
    switch (status) {
        case "paid":
            return "Pago";
        case "partial":
            return "Parcial";
        case "pending":
            return "Pendente";
        default:
            return status;
    }
};

const isOverdue = (dueDate) => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const dueDateObj = new Date(dueDate);
    return dueDateObj < today && dueDateObj.getDate() !== today.getDate();
};

const canDeleteSelected = computed(() => {
    return (
        selectedReceivables.value.length > 0 &&
        selectedReceivables.value.every((r) => !r.order_id)
    );
});

const toggleSelectAll = () => {
    if (
        selectedReceivables.value.length === getSelectableReceivables().length
    ) {
        selectedReceivables.value = [];
        selectedCustomerId.value = null;
    } else {
        const firstReceivable = getSelectableReceivables()[0];
        if (firstReceivable) {
            selectedCustomerId.value = firstReceivable.customer_id;
            selectedReceivables.value = getSelectableReceivables().filter(
                (r) => r.customer_id === firstReceivable.customer_id
            );
        }
    }
};

const getSelectableReceivables = () => {
    return props.receivables?.data?.filter((r) => r.status !== "paid") || [];
};

const isAllSelected = computed(() => {
    const selectableReceivables = getSelectableReceivables();
    if (selectableReceivables.length === 0) return false;

    const selectableFromSameCustomer = selectableReceivables.filter(
        (r) =>
            !selectedCustomerId.value ||
            r.customer_id === selectedCustomerId.value
    );

    return (
        selectedReceivables.value.length > 0 &&
        selectedReceivables.value.length === selectableFromSameCustomer.length
    );
});
</script>

<template>
    <Head title="Recebíveis" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Recebíveis</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Recebíveis' },
                    ]"
                />
            </div>

            <div>
                <Link
                    :href="route('receivables.payments.index')"
                    class="btn btn-secondary mr-2"
                >
                    <i class="fas fa-sm fa-list"></i>
                    &nbsp; Listar Recebidos
                </Link>

                <Link
                    :href="route('receivables.create')"
                    class="btn btn-primary"
                >
                    <i class="fas fa-sm fa-plus"></i>
                    &nbsp; Novo Recebível
                </Link>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Filtros</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tipo de Data</label>
                            <select
                                v-model="filterForm.date_type"
                                class="form-control"
                            >
                                <option value="issue">Data de Emissão</option>
                                <option value="due">Data de Vencimento</option>
                            </select>
                        </div>
                    </div>

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

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select
                                v-model="filterForm.status"
                                class="form-control"
                            >
                                <option value="">Todos</option>
                                <option value="pending">Pendente</option>
                                <option value="partial">Parcial</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
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
            <div class="card-header">Lista de Recebíveis</div>
            <div class="card-body">
                <div v-if="!hasResults" class="alert alert-info">
                    Aplique pelo menos um filtro para visualizar os recebíveis.
                </div>

                <div
                    v-else-if="receivables.data.length === 0"
                    class="alert alert-info"
                >
                    Nenhum recebível encontrado com os filtros aplicados.
                </div>

                <div v-else class="table-responsive">
                    <table
                        class="table table-bordered table-striped table-hover"
                    >
                        <thead>
                            <tr>
                                <th class="col-1 text-center">
                                    <div class="icheck-primary d-inline ml-1">
                                        <input
                                            type="checkbox"
                                            id="checkAll"
                                            :checked="isAllSelected"
                                            @change="toggleSelectAll"
                                        />
                                        <label for="checkAll"></label>
                                    </div>
                                </th>
                                <th class="col-1">Código</th>
                                <th class="col-3">Cliente</th>
                                <th class="col-1">Emissão</th>
                                <th class="col-1">Vencimento</th>
                                <th class="col-1">Valor Total</th>
                                <th class="col-1">Valor Pago</th>
                                <th class="col-1">Valor Saldo</th>
                                <th class="col-1">Status</th>
                                <th class="col-1">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="receivable in receivables.data"
                                :key="receivable.id"
                            >
                                <td class="text-center align-middle">
                                    <div
                                        v-if="receivable.status !== 'paid'"
                                        class="icheck-primary d-inline ml-1"
                                    >
                                        <input
                                            type="checkbox"
                                            :id="`check-${receivable.id}`"
                                            :checked="isSelected(receivable)"
                                            @change="
                                                toggleReceivableSelection(
                                                    receivable
                                                )
                                            "
                                            :disabled="
                                                selectedCustomerId !== null &&
                                                selectedCustomerId !==
                                                    receivable.customer_id
                                            "
                                        />
                                        <label
                                            :for="`check-${receivable.id}`"
                                        ></label>
                                    </div>
                                </td>
                                <td>
                                    {{
                                        formatSequentialId(
                                            receivable.sequential_id
                                        )
                                    }}
                                </td>
                                <td>
                                    {{ receivable.customer.first_name }}
                                    {{ " " }}
                                    {{ receivable.customer.last_name }}
                                </td>
                                <td>{{ formatDate(receivable.issue_date) }}</td>
                                <td
                                    :class="{
                                        'text-danger': isOverdue(
                                            receivable.due_date
                                        ),
                                    }"
                                >
                                    {{ formatDate(receivable.due_date) }}
                                </td>
                                <td>
                                    {{
                                        formatCurrency(receivable.total_amount)
                                    }}
                                </td>
                                <td>
                                    {{ formatCurrency(receivable.paid_amount) }}
                                </td>
                                <td>
                                    {{
                                        formatCurrency(
                                            receivable.remaining_amount
                                        )
                                    }}
                                </td>
                                <td>
                                    <span
                                        :class="[
                                            'badge',
                                            getStatusClass(receivable.status),
                                        ]"
                                    >
                                        {{ getStatusText(receivable.status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="text-nowrap">
                                        <Link
                                            v-if="receivable.status !== 'paid'"
                                            :href="
                                                route(
                                                    'receivables.payments.create',
                                                    { ids: receivable.id }
                                                )
                                            "
                                            class="btn btn-sm btn-primary mr-1"
                                            title="Baixar"
                                        >
                                            Baixar
                                        </Link>

                                        <Link
                                            v-if="receivable.status !== 'paid'"
                                            :href="
                                                route(
                                                    'receivables.edit',
                                                    receivable.id
                                                )
                                            "
                                            class="btn btn-sm btn-secondary mr-1"
                                            title="Editar"
                                        >
                                            Editar
                                        </Link>

                                        <button
                                            v-if="
                                                !receivable.order_id &&
                                                receivable.status !== 'paid'
                                            "
                                            class="btn btn-sm btn-danger"
                                            @click="
                                                deleteIds = receivable.id;
                                                showDeleteModal = true;
                                            "
                                            title="Excluir"
                                        >
                                            Excluir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination
                    v-if="receivables.data && receivables.data.length > 0"
                    :links="receivables.links"
                    @page-change="
                        selectedReceivables = [];
                        selectedCustomerId = null;
                    "
                />

                <div class="d-flex justify-content-end mt-3">
                    <button
                        class="btn btn-primary"
                        @click="openPaymentPage"
                        :disabled="selectedReceivables.length === 0"
                    >
                        <i class="fas fa-sm fa-angle-double-down"></i>
                        &nbsp; Baixar Selecionados
                    </button>

                    <!-- <button
                        class="btn btn-danger"
                        @click="confirmDelete"
                        :disabled="!canDeleteSelected"
                    >
                        <i class="far fa-sm fa-trash-alt"></i>
                        &nbsp; Excluir
                    </button> -->
                </div>
            </div>
        </div>

        <DeleteConfirmation
            v-if="showDeleteModal"
            :visible="showDeleteModal"
            :loading="loading"
            title="Confirmar Exclusão"
            message="Você tem certeza que deseja excluir os recebíveis selecionados?"
            warning="Esta ação não pode ser desfeita."
            delete-route-method="delete"
            :delete-route="handleDelete"
            @cancel="cancelDelete"
            success-redirect="receivables.index"
            success-message="Recebíveis excluídos com sucesso!"
        />
    </AppLayout>
</template>
