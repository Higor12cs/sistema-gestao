<script setup>
import { ref, computed, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputField from "@/Components/InputField.vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    account: Object,
    transactions: Object,
    filters: Object,
});

const selectedTransactions = ref([]);
const allSelected = ref(false);
const isFilterCardCollapsed = ref(false);

const filterForm = useForm({
    start_date: props.filters?.start_date || "",
    end_date: props.filters?.end_date || "",
    type: props.filters?.type || "",
    reconciled: props.filters?.reconciled || "all",
});

const bulkEditForm = useForm({
    transaction_ids: [],
    transaction_date: "",
    reconciled: null,
});

const toggleFilterCard = () => {
    isFilterCardCollapsed.value = !isFilterCardCollapsed.value;
};

watch(
    () => allSelected.value,
    (value) => {
        if (value) {
            selectedTransactions.value = props.transactions.data.map(
                (t) => t.id
            );
        } else {
            selectedTransactions.value = [];
        }
    }
);

watch(
    () => selectedTransactions.value,
    (value) => {
        if (
            value.length === props.transactions.data.length &&
            props.transactions.data.length > 0
        ) {
            allSelected.value = true;
        } else {
            allSelected.value = false;
        }
    },
    { deep: true }
);

const hasSelectedTransactions = computed(() => {
    return selectedTransactions.value.length > 0;
});

const applyFilters = () => {
    router.get(
        route("account-reconciliation.index", props.account.id),
        filterForm.data(),
        {
            preserveState: true,
            replace: true,
        }
    );
};

const resetFilters = () => {
    filterForm.reset();
    router.get(
        route("account-reconciliation.index", props.account.id),
        {},
        {
            preserveState: true,
            replace: true,
        }
    );
};

const bulkReconcile = (status) => {
    if (selectedTransactions.value.length === 0) return;

    bulkEditForm.transaction_ids = selectedTransactions.value;
    bulkEditForm.reconciled = status;
    bulkEditForm.transaction_date = "";

    bulkEditForm.post(route("account-reconciliation.bulk-update"), {
        preserveScroll: true,
        onSuccess: () => {
            selectedTransactions.value = [];
        },
    });
};

const updateSelectedTransactions = () => {
    if (selectedTransactions.value.length === 0) return;

    if (!bulkEditForm.transaction_date && !bulkEditForm.reconciled) {
        alert("Por favor, informe a data ou o status de conciliação.");
        return;
    }

    bulkEditForm.transaction_ids = selectedTransactions.value;

    bulkEditForm.post(route("account-reconciliation.bulk-update"), {
        preserveScroll: true,
        onSuccess: () => {
            selectedTransactions.value = [];
            bulkEditForm.transaction_date = "";
            bulkEditForm.reconciled = null;
        },
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("pt-BR", {
        style: "currency",
        currency: "BRL",
    }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleDateString("pt-BR");
};

const formatSequentialId = (id) => {
    return String(id).padStart(6, "0");
};

const getTransactionOrigin = (transaction) => {
    if (transaction.receivable) {
        if (transaction.receivable.order) {
            return `Pedido #${formatSequentialId(
                transaction.receivable.order.sequential_id
            )} - ${transaction.receivable.customer.first_name} ${
                transaction.receivable.customer.last_name || ""
            }`;
        }
        return `Recebível #${formatSequentialId(
            transaction.receivable.sequential_id
        )} - ${transaction.receivable.customer.first_name} ${
            transaction.receivable.customer.last_name || ""
        }`;
    }

    if (transaction.payable) {
        if (transaction.payable.purchase) {
            return `Compra #${formatSequentialId(
                transaction.payable.purchase.sequential_id
            )} - ${transaction.payable.supplier.first_name} ${
                transaction.payable.supplier.last_name || ""
            }`;
        }
        return `Pagável #${formatSequentialId(
            transaction.payable.sequential_id
        )} - ${transaction.payable.supplier.first_name} ${
            transaction.payable.supplier.last_name || ""
        }`;
    }

    return "Lançamento Manual";
};
</script>

<template>
    <Head title="Conciliação Bancária" />
    <AuthenticatedLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Conciliação Bancária - {{ account.name }}</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Contas', routeName: 'accounts.index' },
                        {
                            label: 'Conciliação Bancária',
                            routeName: 'account-reconciliation.select',
                        },
                        { label: account.name },
                    ]"
                />
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div
                class="card-header d-flex justify-content-between align-items-center"
                style="cursor: pointer"
                @click="toggleFilterCard"
            >
                <div class="d-flex justify-content-between w-100">
                    <div>Filtros</div>
                    <div class="d-flex align-items-center">
                        <strong class="mr-3">Saldo Atual:</strong>
                        {{ formatCurrency(account.current_balance) }}
                        <i
                            :class="
                                isFilterCardCollapsed
                                    ? 'fas fa-sm fa-plus ml-3'
                                    : 'fas fa-sm fa-minus ml-3'
                            "
                        ></i>
                    </div>
                </div>
            </div>
            <div class="card-body" v-show="!isFilterCardCollapsed">
                <div class="row">
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
                            <label for="type">Tipo</label>
                            <select
                                id="type"
                                v-model="filterForm.type"
                                class="form-control"
                            >
                                <option value="">Todos</option>
                                <option value="income">Entrada</option>
                                <option value="expense">Saída</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="reconciled">Conciliado</label>
                            <select
                                id="reconciled"
                                v-model="filterForm.reconciled"
                                class="form-control"
                            >
                                <option value="all">Todos</option>
                                <option value="yes">Sim</option>
                                <option value="no">Não</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 d-flex justify-content-end">
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
            <div
                class="card-header d-flex justify-content-between align-items-center"
            >
                <div>Transações</div>
                <div v-if="hasSelectedTransactions">
                    <span class="badge badge-primary mr-2"
                        >{{ selectedTransactions.length }} selecionados</span
                    >
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table
                        class="table table-bordered table-striped table-hover"
                    >
                        <thead>
                            <tr>
                                <th class="col-1 text-center">
                                    <div
                                        class="form-check d-flex justify-content-center"
                                    >
                                        <div
                                            class="icheck-primary d-inline mr-3"
                                        >
                                            <input
                                                type="checkbox"
                                                id="select-all"
                                                v-model="allSelected"
                                                :disabled="
                                                    transactions.data.length ===
                                                    0
                                                "
                                            />
                                            <label for="select-all"></label>
                                        </div>
                                    </div>
                                </th>
                                <th class="col-1">Código</th>
                                <th class="col-2">Data</th>
                                <th class="col-3">Descrição</th>
                                <th class="col-3">Origem</th>
                                <th class="col-1">Valor</th>
                                <th class="col-1">Conciliado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(
                                    transaction, index
                                ) in transactions.data"
                                :key="transaction.id"
                            >
                                <td>
                                    <div
                                        class="form-check d-flex justify-content-center"
                                    >
                                        <div
                                            class="icheck-primary d-inline mr-3"
                                        >
                                            <input
                                                type="checkbox"
                                                :id="
                                                    'transaction-' +
                                                    transaction.id
                                                "
                                                :value="transaction.id"
                                                v-model="selectedTransactions"
                                            />
                                            <label
                                                :for="
                                                    'transaction-' +
                                                    transaction.id
                                                "
                                            ></label>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{
                                        formatSequentialId(
                                            transaction.sequential_id
                                        )
                                    }}
                                </td>
                                <td>
                                    {{
                                        formatDate(transaction.transaction_date)
                                    }}
                                </td>
                                <td>{{ transaction.description }}</td>
                                <td>{{ getTransactionOrigin(transaction) }}</td>
                                <td
                                    :class="
                                        transaction.type === 'income'
                                            ? 'text-success'
                                            : 'text-danger'
                                    "
                                >
                                    {{ formatCurrency(transaction.amount) }}
                                </td>
                                <td>
                                    <span
                                        class="badge"
                                        :class="
                                            transaction.reconciled
                                                ? 'badge-success'
                                                : 'badge-danger'
                                        "
                                    >
                                        {{
                                            transaction.reconciled
                                                ? "Sim"
                                                : "Não"
                                        }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="transactions.data.length === 0">
                                <td colspan="7" class="text-center">
                                    Nenhuma transação encontrada.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :links="transactions.links" class="mt-3" />
            </div>
        </div>

        <!-- Seção de ações em massa -->
        <div class="card mt-4" v-if="hasSelectedTransactions">
            <div class="card-header">
                Atualizar {{ selectedTransactions.length }} Transações
                Selecionadas
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="transaction_date"
                                >Data da Transação</label
                            >
                            <input
                                type="date"
                                id="transaction_date"
                                class="form-control"
                                v-model="bulkEditForm.transaction_date"
                            />
                            <small class="form-text text-muted"
                                >Deixe em branco para manter as datas
                                atuais.</small
                            >
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <div>
                        <label>Status de Conciliação</label>
                        <div>
                            <button
                                class="btn btn-success mr-2"
                                @click="bulkReconcile(true)"
                            >
                                <i class="fas fa-check-double"></i>
                                &nbsp;
                                Conciliar Todos
                            </button>
                            <button
                                class="btn btn-danger"
                                @click="bulkReconcile(false)"
                            >
                                <i class="fas fa-times"></i>
                                &nbsp;
                                Remover Conciliação
                            </button>
                        </div>
                    </div>

                    <button
                        class="btn btn-primary mt-auto"
                        @click="updateSelectedTransactions"
                        :disabled="bulkEditForm.processing"
                    >
                        <span v-if="bulkEditForm.processing">
                            <span
                                class="spinner-border spinner-border-sm"
                                role="status"
                                aria-hidden="true"
                            ></span>
                            Atualizando...
                        </span>
                        <span v-else>
                            <i class="fas fa-save"></i>
                            &nbsp;
                            Atualizar Transações Selecionadas
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.table th,
.table td {
    vertical-align: middle;
}
</style>
