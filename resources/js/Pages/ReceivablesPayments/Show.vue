<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {
    formatCurrency,
    formatDate,
    formatSequentialId,
} from "@/Utils/Formatters.js";

const props = defineProps({
    payment: Object,
});
</script>

<template>
    <Head title="Detalhes do Pagamento" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Detalhes do Pagamento</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Recebíveis', routeName: 'receivables.index' },
                        {
                            label: 'Pagamentos',
                            routeName: 'receivables.payments.index',
                        },
                        {
                            label: `Pagamento #${formatSequentialId(
                                payment.id,
                            )}`,
                        },
                    ]"
                />
            </div>

            <div>
                <Link
                    :href="route('receivables.payments.index')"
                    class="btn btn-secondary"
                >
                    <i class="fas fa-sm fa-arrow-left"></i>
                    &nbsp; Voltar
                </Link>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">Informações do Pagamento</div>
                    <div class="card-body px-0">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th class="pl-3" style="width: 200px">
                                        Código:
                                    </th>
                                    <td class="pr-3 text-right">
                                        {{ formatSequentialId(payment.id) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Data de Pagamento:</th>
                                    <td class="pr-3 text-right">
                                        {{ formatDate(payment.payment_date) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Método de Pagamento:</th>
                                    <td class="pr-3 text-right">
                                        {{ payment.payment_method.name }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Conta:</th>
                                    <td class="pr-3 text-right">
                                        {{ payment.account.name }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Valor Total:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            formatCurrency(payment.total_amount)
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Valor Pago:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            formatCurrency(payment.paid_amount)
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Acréscimos:</th>
                                    <td class="pr-3 text-right">
                                        {{ formatCurrency(payment.fees) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Descontos:</th>
                                    <td class="pr-3 text-right">
                                        {{ formatCurrency(payment.discount) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Valor Efetivo:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            formatCurrency(
                                                (payment.paid_amount || 0) +
                                                    (payment.fees || 0) -
                                                    (payment.discount || 0),
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr v-if="payment.notes">
                                    <th class="pl-3">Observações:</th>
                                    <td class="pr-3 text-right">
                                        {{ payment.notes }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">Informações do Recebível</div>
                    <div class="card-body px-0">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th class="pl-3" style="width: 200px">
                                        Código:
                                    </th>
                                    <td class="pr-3 text-right">
                                        {{
                                            formatSequentialId(
                                                payment.receivable.id,
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Cliente:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            payment.receivable.customer
                                                .first_name ||
                                            payment.receivable.customer.name
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Data de Emissão:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            formatDate(
                                                payment.receivable.issue_date,
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Data de Vencimento:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            formatDate(
                                                payment.receivable.due_date,
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Valor Total:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            formatCurrency(
                                                payment.receivable.total_amount,
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Valor Pago:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            formatCurrency(
                                                payment.receivable.paid_amount,
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Saldo Restante:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            formatCurrency(
                                                payment.receivable
                                                    .remaining_amount,
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Status:</th>
                                    <td class="pr-3 text-right">
                                        <span
                                            :class="[
                                                'badge',
                                                payment.receivable.status ===
                                                'paid'
                                                    ? 'badge-primary'
                                                    : payment.receivable
                                                            .status ===
                                                        'partial'
                                                      ? 'badge-secondary'
                                                      : 'badge-warning',
                                            ]"
                                        >
                                            {{
                                                payment.receivable.status ===
                                                "paid"
                                                    ? "Pago"
                                                    : payment.receivable
                                                            .status ===
                                                        "partial"
                                                      ? "Parcial"
                                                      : "Pendente"
                                            }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Informações da Transação</div>
                    <div class="card-body px-0">
                        <table class="table" v-if="payment.transaction">
                            <tbody>
                                <tr>
                                    <th class="pl-3" style="width: 200px">
                                        Código:
                                    </th>
                                    <td class="pr-3 text-right">
                                        {{
                                            formatSequentialId(
                                                payment.transaction.id,
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Data:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            formatDate(
                                                payment.transaction
                                                    .transaction_date,
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Tipo:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            payment.transaction.type ===
                                            "income"
                                                ? "Receita"
                                                : "Despesa"
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Valor:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            formatCurrency(
                                                payment.transaction.amount,
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Conciliada:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            payment.transaction.reconciled
                                                ? "Sim"
                                                : "Não"
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-else class="alert alert-info">
                            Nenhuma transação associada a este pagamento.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
