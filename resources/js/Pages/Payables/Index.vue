<script setup>
import { ref, computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router, Link } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";
import DeleteConfirmation from "@/Components/DeleteConfirmation.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Select2 from "@/Components/Select2.vue";
import InputField from "@/Components/InputField.vue";

const props = defineProps({
    payables: Object,
    filters: Object,
    hasResults: Boolean,
    selectedSupplier: Object,
});

const filterForm = useForm({
    start_date: props.filters?.start_date || "",
    end_date: props.filters?.end_date || "",
    date_type: props.filters?.date_type || "due",
    supplier_id: props.filters?.supplier_id || "",
    status: props.filters?.status || "",
});

const loading = ref(false);
const showDeleteModal = ref(false);
const deleteIds = ref([]);
const selectedPayables = ref([]);
const selectedSupplierId = ref(null);

const applyFilters = () => {
    selectedPayables.value = [];
    selectedSupplierId.value = null;

    router.get(route("payables.index"), filterForm.data(), {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    filterForm.reset();
    selectedPayables.value = [];
    selectedSupplierId.value = null;

    router.get(
        route("payables.index"),
        {},
        {
            preserveState: true,
            replace: true,
        }
    );
};

const togglePayableSelection = (payable) => {
    const index = selectedPayables.value.findIndex((r) => r.id === payable.id);

    if (index === -1) {
        if (
            selectedPayables.value.length > 0 &&
            selectedPayables.value[0].supplier_id !== payable.supplier_id
        ) {
            alert("Só é possível selecionar pagáveis do mesmo fornecedor.");
            return;
        }

        selectedPayables.value.push(payable);
        if (!selectedSupplierId.value) {
            selectedSupplierId.value = payable.supplier_id;
        }
    } else {
        selectedPayables.value.splice(index, 1);
        if (selectedPayables.value.length === 0) {
            selectedSupplierId.value = null;
        }
    }
};

const isSelected = (payable) => {
    return selectedPayables.value.some((r) => r.id === payable.id);
};

const openPaymentPage = () => {
    if (selectedPayables.value.length === 0) {
        alert("Por favor, selecione pelo menos um pagável para pagamento.");
        return;
    }

    const ids = selectedPayables.value.map((r) => r.id).join(",");
    window.location.href = route("payables.payments.create", { ids });
};

const confirmDelete = () => {
    if (selectedPayables.value.length === 0) {
        alert("Por favor, selecione pelo menos um pagável para exclusão.");
        return;
    }

    deleteIds.value = selectedPayables.value.map((r) => r.id).join(",");
    showDeleteModal.value = true;
};

const handleDelete = () => {
    loading.value = true;
    return route("payables.destroy", { ids: deleteIds.value });
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    deleteIds.value = null;
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
        selectedPayables.value.length > 0 &&
        selectedPayables.value.every((r) => !r.purchase_id)
    );
});

const formatSequentialId = (id) => {
    return String(id).padStart(6, "0");
};

const toggleSelectAll = () => {
    if (selectedPayables.value.length === getSelectablePayables().length) {
        selectedPayables.value = [];
        selectedSupplierId.value = null;
    } else {
        const firstPayable = getSelectablePayables()[0];
        if (firstPayable) {
            selectedSupplierId.value = firstPayable.supplier_id;
            selectedPayables.value = getSelectablePayables().filter(
                (r) => r.supplier_id === firstPayable.supplier_id
            );
        }
    }
};

const getSelectablePayables = () => {
    return props.payables?.data?.filter((r) => r.status !== "paid") || [];
};

const isAllSelected = computed(() => {
    const selectablePayables = getSelectablePayables();
    if (selectablePayables.length === 0) return false;

    const selectableFromSameSupplier = selectablePayables.filter(
        (r) =>
            !selectedSupplierId.value ||
            r.supplier_id === selectedSupplierId.value
    );

    return (
        selectedPayables.value.length > 0 &&
        selectedPayables.value.length === selectableFromSameSupplier.length
    );
});
</script>

<template>
    <Head title="Pagáveis" />
    <AuthenticatedLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Pagáveis</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Pagáveis' },
                    ]"
                />
            </div>

            <div>
                <Link
                    :href="route('payables.payments.index')"
                    class="btn btn-secondary mr-2"
                >
                    <i class="fas fa-sm fa-list"></i>
                    &nbsp; Listar Pagos
                </Link>

                <Link :href="route('payables.create')" class="btn btn-primary">
                    <i class="fas fa-sm fa-plus"></i>
                    &nbsp; Novo Pagável
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
                            label="Fornecedor"
                            v-model="filterForm.supplier_id"
                            :search-url="route('api.suppliers.search')"
                            value-key="id"
                            label-key="name"
                            placeholder="Pesquisar Fornecedor"
                            :initial-options="
                                selectedSupplier ? [selectedSupplier] : []
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
            <div class="card-header">Lista de Pagáveis</div>
            <div class="card-body">
                <div v-if="!hasResults" class="alert alert-info">
                    Aplique pelo menos um filtro para visualizar os pagáveis.
                </div>

                <div
                    v-else-if="payables.data.length === 0"
                    class="alert alert-info"
                >
                    Nenhum pagável encontrado com os filtros aplicados.
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
                                <th class="col-3">Fornecedor</th>
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
                                v-for="payable in payables.data"
                                :key="payable.id"
                            >
                                <td class="text-center align-middle">
                                    <div
                                        v-if="payable.status !== 'paid'"
                                        class="icheck-primary d-inline ml-1"
                                    >
                                        <input
                                            type="checkbox"
                                            :id="`check-${payable.id}`"
                                            :checked="isSelected(payable)"
                                            @change="
                                                togglePayableSelection(payable)
                                            "
                                            :disabled="
                                                selectedSupplierId !== null &&
                                                selectedSupplierId !==
                                                    payable.supplier_id
                                            "
                                        />
                                        <label
                                            :for="`check-${payable.id}`"
                                        ></label>
                                    </div>
                                </td>
                                <td>
                                    {{
                                        formatSequentialId(
                                            payable.sequential_id
                                        )
                                    }}
                                </td>
                                <td>
                                    {{ payable.supplier.first_name }}
                                    {{ " " }}
                                    {{ payable.supplier.last_name }}
                                </td>
                                <td>{{ formatDate(payable.issue_date) }}</td>
                                <td
                                    :class="{
                                        'text-danger': isOverdue(
                                            payable.due_date
                                        ),
                                    }"
                                >
                                    {{ formatDate(payable.due_date) }}
                                </td>
                                <td>
                                    {{ formatCurrency(payable.total_amount) }}
                                </td>
                                <td>
                                    {{ formatCurrency(payable.paid_amount) }}
                                </td>
                                <td>
                                    {{
                                        formatCurrency(payable.remaining_amount)
                                    }}
                                </td>
                                <td>
                                    <span
                                        :class="[
                                            'badge',
                                            getStatusClass(payable.status),
                                        ]"
                                    >
                                        {{ getStatusText(payable.status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="text-nowrap">
                                        <Link
                                            v-if="payable.status !== 'paid'"
                                            :href="
                                                route(
                                                    'payables.payments.create',
                                                    { ids: payable.id }
                                                )
                                            "
                                            class="btn btn-sm btn-primary mr-1"
                                            title="Baixar"
                                        >
                                            Baixar
                                        </Link>

                                        <Link
                                            v-if="payable.status !== 'paid'"
                                            :href="
                                                route(
                                                    'payables.edit',
                                                    payable.sequential_id
                                                )
                                            "
                                            class="btn btn-sm btn-secondary mr-1"
                                            title="Editar"
                                        >
                                            Editar
                                        </Link>

                                        <button
                                            v-if="
                                                !payable.purchase_id &&
                                                payable.status !== 'paid'
                                            "
                                            class="btn btn-sm btn-danger"
                                            @click="
                                                deleteIds = payable.id;
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
                    v-if="payables.data && payables.data.length > 0"
                    :links="payables.links"
                    @page-change="
                        selectedPayables = [];
                        selectedSupplierId = null;
                    "
                />

                <div class="d-flex justify-content-end mt-3">
                    <button
                        class="btn btn-primary"
                        @click="openPaymentPage"
                        :disabled="selectedPayables.length === 0"
                    >
                        <i class="fas fa-sm fa-arrow-down"></i>
                        &nbsp; Baixar Selecionados
                    </button>
                </div>
            </div>
        </div>

        <DeleteConfirmation
            v-if="showDeleteModal"
            :visible="showDeleteModal"
            :loading="loading"
            title="Confirmar Exclusão"
            message="Você tem certeza que deseja excluir os pagáveis selecionados?"
            warning="Esta ação não pode ser desfeita."
            delete-route-method="delete"
            :delete-route="handleDelete"
            @cancel="cancelDelete"
            success-redirect="payables.index"
            success-message="Pagáveis excluídos com sucesso!"
        />
    </AuthenticatedLayout>
</template>
