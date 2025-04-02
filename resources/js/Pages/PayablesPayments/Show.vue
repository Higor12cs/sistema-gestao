<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";

const props = defineProps({
    payment: Object,
});

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
</script>

<template>
    <Head title="Detalhes do Pagamento" />
    <AuthenticatedLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Detalhes do Pagamento</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Pagáveis', routeName: 'payables.index' },
                        {
                            label: 'Pagamentos',
                            routeName: 'payables.payments.index',
                        },
                        {
                            label: `Pagamento #${formatSequentialId(
                                payment.sequential_id
                            )}`,
                        },
                    ]"
                />
            </div>

            <div>
                <Link
                    :href="route('payables.payments.index')"
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
                                        {{
                                            formatSequentialId(
                                                payment.sequential_id
                                            )
                                        }}
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
                                                    (payment.discount || 0)
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
                    <div class="card-header">Informações do Pagável</div>
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
                                                payment.payable.sequential_id
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Fornecedor:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            payment.payable.supplier
                                                .first_name ||
                                            payment.payable.supplier.name
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Data de Emissão:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            formatDate(
                                                payment.payable.issue_date
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Data de Vencimento:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            formatDate(payment.payable.due_date)
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Valor Total:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            formatCurrency(
                                                payment.payable.total_amount
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Valor Pago:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            formatCurrency(
                                                payment.payable.paid_amount
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Saldo Restante:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            formatCurrency(
                                                payment.payable.remaining_amount
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
                                                payment.payable.status ===
                                                'paid'
                                                    ? 'badge-primary'
                                                    : payment.payable.status ===
                                                      'partial'
                                                    ? 'badge-secondary'
                                                    : 'badge-warning',
                                            ]"
                                        >
                                            {{
                                                payment.payable.status ===
                                                "paid"
                                                    ? "Pago"
                                                    : payment.payable.status ===
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
                                                payment.transaction
                                                    .sequential_id
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
                                                    .transaction_date
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Tipo:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            payment.transaction.type ===
                                            "expense"
                                                ? "Despesa"
                                                : "Receita"
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-3">Valor:</th>
                                    <td class="pr-3 text-right">
                                        {{
                                            formatCurrency(
                                                payment.transaction.amount
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
    </AuthenticatedLayout>
</template>
