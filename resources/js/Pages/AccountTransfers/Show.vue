<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";

const props = defineProps({
    transfer: Object,
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

const formatDateTime = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return (
        date.toLocaleDateString("pt-BR") +
        " " +
        date.toLocaleTimeString("pt-BR")
    );
};

const formatSequentialId = (id) => {
    return String(id).padStart(6, "0");
};
</script>

<template>
    <Head title="Detalhes da Transferência" />
    <AuthenticatedLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Detalhes da Transferência</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        {
                            label: 'Transferências',
                            routeName: 'account-transfers.index',
                        },
                        {
                            label: `Transferência #${formatSequentialId(
                                transfer.sequential_id
                            )}`,
                        },
                    ]"
                />
            </div>

            <div>
                <Link
                    :href="route('account-transfers.index')"
                    class="btn btn-secondary"
                >
                    <i class="fas fa-sm fa-arrow-left"></i>
                    &nbsp; Voltar
                </Link>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">Informações da Transferência</div>
                    <div class="card-body px-0 pb-0">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th class="pl-4" style="width: 40%">
                                        Código:
                                    </th>
                                    <td>
                                        {{
                                            formatSequentialId(
                                                transfer.sequential_id
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Data da Transferência:</th>
                                    <td>
                                        {{ formatDate(transfer.transfer_date) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Conta de Origem:</th>
                                    <td>{{ transfer.source_account.name }}</td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Conta de Destino:</th>
                                    <td>
                                        {{ transfer.destination_account.name }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Valor:</th>
                                    <td>
                                        {{ formatCurrency(transfer.amount) }}
                                    </td>
                                </tr>
                                <tr v-if="transfer.notes">
                                    <th class="pl-4">Observações:</th>
                                    <td>{{ transfer.notes }}</td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Criado Por:</th>
                                    <td>{{ transfer.created_by.name }}</td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Data de Criação:</th>
                                    <td>
                                        {{
                                            formatDateTime(transfer.created_at)
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">Transação de Débito</div>
                    <div class="card-body px-0 pb-0">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th class="pl-4" style="width: 40%">
                                        Código:
                                    </th>
                                    <td>
                                        {{
                                            formatSequentialId(
                                                transfer.debit_transaction
                                                    .sequential_id
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Conta:</th>
                                    <td>{{ transfer.source_account.name }}</td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Tipo:</th>
                                    <td>
                                        <span class="badge badge-danger"
                                            >Saída</span
                                        >
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Valor:</th>
                                    <td>
                                        {{
                                            formatCurrency(
                                                transfer.debit_transaction
                                                    .amount
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Descrição:</th>
                                    <td>
                                        {{
                                            transfer.debit_transaction
                                                .description
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Conciliada:</th>
                                    <td>
                                        <span
                                            :class="
                                                transfer.debit_transaction
                                                    .reconciled
                                                    ? 'badge badge-success'
                                                    : 'badge badge-warning'
                                            "
                                        >
                                            {{
                                                transfer.debit_transaction
                                                    .reconciled
                                                    ? "Sim"
                                                    : "Não"
                                            }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">Transação de Crédito</div>
                    <div class="card-body px-0 pb-0">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th class="pl-4" style="width: 40%">
                                        Código:
                                    </th>
                                    <td>
                                        {{
                                            formatSequentialId(
                                                transfer.credit_transaction
                                                    .sequential_id
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Conta:</th>
                                    <td>
                                        {{ transfer.destination_account.name }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Tipo:</th>
                                    <td>
                                        <span class="badge badge-success"
                                            >Entrada</span
                                        >
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Valor:</th>
                                    <td>
                                        {{
                                            formatCurrency(
                                                transfer.credit_transaction
                                                    .amount
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Descrição:</th>
                                    <td>
                                        {{
                                            transfer.credit_transaction
                                                .description
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Conciliada:</th>
                                    <td>
                                        <span
                                            :class="
                                                transfer.credit_transaction
                                                    .reconciled
                                                    ? 'badge badge-success'
                                                    : 'badge badge-warning'
                                            "
                                        >
                                            {{
                                                transfer.credit_transaction
                                                    .reconciled
                                                    ? "Sim"
                                                    : "Não"
                                            }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
