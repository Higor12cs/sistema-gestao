<script setup>
import { ref } from "vue";
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
} from "@/Utils/Formatters";

const props = defineProps({
    payments: Object,
    filters: Object,
});

const filterForm = useForm({
    start_date: props.filters?.start_date || "",
    end_date: props.filters?.end_date || "",
    supplier_id: props.filters?.supplier_id || "",
});

const loading = ref(false);
const showDeleteModal = ref(false);
const deletePaymentId = ref(null);

const applyFilters = () => {
    router.get(route("payables.payments.index"), filterForm.data(), {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    filterForm.reset();
    router.get(
        route("payables.payments.index"),
        {},
        {
            preserveState: true,
            replace: true,
        }
    );
};

const confirmDelete = (paymentId) => {
    deletePaymentId.value = paymentId;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    loading.value = true;
    return route("payables.payments.destroy", deletePaymentId.value);
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    deletePaymentId.value = null;
};
</script>

<template>
    <Head title="Pagamentos de Pagáveis" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Pagamentos de Pagáveis</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Pagáveis', routeName: 'payables.index' },
                        { label: 'Pagamentos' },
                    ]"
                />
            </div>

            <div>
                <Link :href="route('payables.index')" class="btn btn-secondary">
                    <i class="fas fa-sm fa-arrow-left"></i>
                    &nbsp; Voltar
                </Link>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Filtros</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <InputField
                            id="start_date"
                            label="Data Inicial de Pagamento"
                            v-model="filterForm.start_date"
                            type="date"
                        />
                    </div>

                    <div class="col-md-3">
                        <InputField
                            id="end_date"
                            label="Data Final de Pagamento"
                            v-model="filterForm.end_date"
                            type="date"
                        />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <Select2
                            label="Fornecedor"
                            v-model="filterForm.supplier_id"
                            :search-url="route('api.suppliers.search')"
                            value-key="id"
                            label-key="name"
                            placeholder="Pesquisar Fornecedor"
                            :initial-options="[]"
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
            <div class="card-header">Lista de Pagamentos</div>
            <div class="card-body">
                <div v-if="payments.data.length === 0" class="alert alert-info">
                    Nenhum pagamento encontrado com os filtros aplicados.
                </div>

                <div v-else class="table-responsive">
                    <table
                        class="table table-bordered table-striped table-hover"
                    >
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Pagável</th>
                                <th>Fornecedor</th>
                                <th>Data Pagamento</th>
                                <th>Método</th>
                                <th>Conta</th>
                                <th>Valor Total</th>
                                <th>Valor Pago</th>
                                <th>Acréscimos</th>
                                <th>Descontos</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="payment in payments.data"
                                :key="payment.id"
                            >
                                <td>
                                    {{
                                        formatSequentialId(
                                            payment.sequential_id
                                        )
                                    }}
                                </td>
                                <td>
                                    {{
                                        formatSequentialId(
                                            payment.payable.sequential_id
                                        )
                                    }}
                                </td>
                                <td>
                                    {{ payment.payable.supplier.first_name }}
                                    {{ " " }}
                                    {{ payment.payable.supplier.last_name }}
                                </td>
                                <td>
                                    {{ formatDate(payment.payment_date) }}
                                </td>
                                <td>
                                    {{ payment.payment_method.name }}
                                </td>
                                <td>
                                    {{ payment.account.name }}
                                </td>
                                <td>
                                    {{ formatCurrency(payment.total_amount) }}
                                </td>
                                <td>
                                    {{ formatCurrency(payment.paid_amount) }}
                                </td>
                                <td>
                                    {{ formatCurrency(payment.fees) }}
                                </td>
                                <td>
                                    {{ formatCurrency(payment.discount) }}
                                </td>
                                <td>
                                    <div class="text-nowrap">
                                        <Link
                                            :href="
                                                route(
                                                    'payables.payments.show',
                                                    payment.sequential_id
                                                )
                                            "
                                            class="btn btn-sm btn-secondary mr-1"
                                            title="Visualizar"
                                        >
                                            Visualizar
                                        </Link>

                                        <button
                                            class="btn btn-sm btn-danger"
                                            @click="confirmDelete(payment.id)"
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
                    v-if="payments.data && payments.data.length > 0"
                    :links="payments.links"
                />
            </div>
        </div>

        <DeleteConfirmation
            v-if="showDeleteModal"
            :visible="showDeleteModal"
            :loading="loading"
            title="Confirmar Exclusão"
            message="Você tem certeza que deseja excluir este pagamento? O saldo do pagável será restaurado."
            warning="Esta ação não pode ser desfeita."
            delete-route-method="delete"
            :delete-route="handleDelete"
            @cancel="cancelDelete"
            success-redirect="payables.payments.index"
            success-message="Pagamento excluído e saldo do pagável restaurado com sucesso!"
        />
    </AppLayout>
</template>
