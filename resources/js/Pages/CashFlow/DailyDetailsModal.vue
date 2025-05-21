<script setup>
import { ref, watch, onMounted } from "vue";
import { formatCurrency, formatDate } from "@/Utils/Formatters.js";

const props = defineProps({
    visible: Boolean,
    date: String,
    transactions: Array,
    receivables: Array,
    payables: Array,
    isLoading: Boolean,
});

const emit = defineEmits(["close"]);
const isVisible = ref(props.visible);

const initializeModal = () => {
    if (window.$) {
        $("#dailyDetailsModal").modal({
            // backdrop: "static",
            // keyboard: false,
        });
        $("#dailyDetailsModal").on("hidden.bs.modal", () => emit("close"));
    }
};

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
        if (value && window.$) {
            setTimeout(() => $("#dailyDetailsModal").modal("show"), 500);
        } else if (!value && window.$) {
            $("#dailyDetailsModal").modal("hide");
        }
    }
);

const closeModal = () => {
    if (window.$) $("#dailyDetailsModal").modal("hide");
    emit("close");
};

onMounted(() => {
    initializeModal();
});
</script>

<template>
    <div
        class="modal fade"
        id="dailyDetailsModal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="dailyDetailsModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dailyDetailsModalLabel">
                        Detalhes do dia {{ formatDate(date) }}
                    </h5>
                    <button
                        type="button"
                        class="close"
                        @click="closeModal"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div v-if="isLoading" class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Carregando...</span>
                        </div>
                        <p class="mt-2">Carregando...</p>
                    </div>
                    <div v-else>
                        <ul
                            class="nav nav-tabs"
                            id="detailsTabs"
                            role="tablist"
                        >
                            <li class="nav-item">
                                <a
                                    class="nav-link active"
                                    id="receivables-tab"
                                    data-toggle="tab"
                                    href="#receivables"
                                    role="tab"
                                >
                                    Recebíveis
                                </a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    id="payables-tab"
                                    data-toggle="tab"
                                    href="#payables"
                                    role="tab"
                                >
                                    Pagáveis
                                </a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    id="transactions-tab"
                                    data-toggle="tab"
                                    href="#transactions"
                                    role="tab"
                                >
                                    Transações
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content mt-3">
                            <div
                                class="tab-pane fade show active"
                                id="receivables"
                                role="tabpanel"
                            >
                                <div
                                    v-if="receivables.length === 0"
                                    class="alert alert-info"
                                >
                                    Nenhum recebível previsto para este dia.
                                </div>
                                <div v-else class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Cliente</th>
                                                <th>Descrição</th>
                                                <th>Método</th>
                                                <th>Status</th>
                                                <th class="text-right">
                                                    Valor
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="receivable in receivables"
                                                :key="receivable.id"
                                            >
                                                <td>
                                                    {{
                                                        receivable.customer
                                                            ?.name
                                                    }}
                                                </td>
                                                <td>
                                                    {{ receivable.description }}
                                                </td>
                                                <td>
                                                    {{
                                                        receivable
                                                            .payment_method
                                                            ?.name
                                                    }}
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge"
                                                        :class="{
                                                            'bg-success':
                                                                receivable.status ===
                                                                'paid',
                                                            'bg-warning':
                                                                receivable.status ===
                                                                'pending',
                                                            'bg-danger':
                                                                receivable.status ===
                                                                'overdue',
                                                        }"
                                                    >
                                                        {{
                                                            receivable.status ===
                                                            "paid"
                                                                ? "Pago"
                                                                : receivable.status ===
                                                                  "pending"
                                                                ? "Pendente"
                                                                : "Vencido"
                                                        }}
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    {{
                                                        formatCurrency(
                                                            receivable.remaining_amount
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div
                                class="tab-pane fade"
                                id="payables"
                                role="tabpanel"
                            >
                                <div
                                    v-if="payables.length === 0"
                                    class="alert alert-info"
                                >
                                    Nenhum pagável previsto para este dia.
                                </div>
                                <div v-else class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Fornecedor</th>
                                                <th>Descrição</th>
                                                <th>Método</th>
                                                <th>Status</th>
                                                <th class="text-right">
                                                    Valor
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="payable in payables"
                                                :key="payable.id"
                                            >
                                                <td>
                                                    {{ payable.supplier?.name }}
                                                </td>
                                                <td>
                                                    {{ payable.description }}
                                                </td>
                                                <td>
                                                    {{
                                                        payable.payment_method
                                                            ?.name
                                                    }}
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge"
                                                        :class="{
                                                            'bg-success':
                                                                payable.status ===
                                                                'paid',
                                                            'bg-warning':
                                                                payable.status ===
                                                                'pending',
                                                            'bg-danger':
                                                                payable.status ===
                                                                'overdue',
                                                        }"
                                                    >
                                                        {{
                                                            payable.status ===
                                                            "paid"
                                                                ? "Pago"
                                                                : payable.status ===
                                                                  "pending"
                                                                ? "Pendente"
                                                                : "Vencido"
                                                        }}
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    {{
                                                        formatCurrency(
                                                            payable.remaining_amount
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div
                                class="tab-pane"
                                id="transactions"
                                role="tabpanel"
                            >
                                <div
                                    v-if="transactions.length === 0"
                                    class="alert alert-info"
                                >
                                    Nenhuma transação realizada neste dia.
                                </div>
                                <div v-else class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Descrição</th>
                                                <th>Conta</th>
                                                <th>Tipo</th>
                                                <th class="text-right">
                                                    Valor
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="transaction in transactions"
                                                :key="transaction.id"
                                            >
                                                <td>
                                                    {{
                                                        transaction.description
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        transaction.account
                                                            ?.name
                                                    }}
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge"
                                                        :class="
                                                            transaction.type ===
                                                            'income'
                                                                ? 'bg-success'
                                                                : 'bg-danger'
                                                        "
                                                    >
                                                        {{
                                                            transaction.type ===
                                                            "income"
                                                                ? "Entrada"
                                                                : "Saída"
                                                        }}
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    {{
                                                        formatCurrency(
                                                            transaction.amount
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        @click="closeModal"
                    >
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
