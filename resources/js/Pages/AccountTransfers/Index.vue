<script setup>
import { ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, useForm, router, Link } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";
import DeleteConfirmation from "@/Components/DeleteConfirmation.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputField from "@/Components/InputField.vue";
import {
    formatCurrency,
    formatDate,
    formatSequentialId,
} from "@/Utils/Formatters.js";

const props = defineProps({
    transfers: Object,
    filters: Object,
});

const searchForm = useForm({
    start_date: props.filters?.start_date || "",
    end_date: props.filters?.end_date || "",
});

const loading = ref(false);
const showDeleteModal = ref(false);
const deleteTransferId = ref(null);

const applyDateFilter = () => {
    router.get(
        route("account-transfers.index"),
        {
            start_date: searchForm.start_date,
            end_date: searchForm.end_date,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const resetFilter = () => {
    searchForm.start_date = "";
    searchForm.end_date = "";
    router.get(
        route("account-transfers.index"),
        {},
        {
            preserveState: true,
            replace: true,
        }
    );
};

const confirmDelete = (transfer) => {
    deleteTransferId.value = transfer.id;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    loading.value = true;
    return route("account-transfers.destroy", deleteTransferId.value);
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    deleteTransferId.value = null;
};
</script>

<template>
    <Head title="Transferências" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Transferências</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Transferências' },
                    ]"
                />
            </div>

            <Link
                :href="route('account-transfers.create')"
                class="btn btn-primary mb-auto"
            >
                <i class="fas fa-sm fa-plus"></i>
                &nbsp; Nova Transferência
            </Link>
        </div>

        <!-- Card de Filtros -->
        <div class="card mb-4">
            <div class="card-header">Filtros</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <InputField
                            id="start_date"
                            label="Data Inicial"
                            v-model="searchForm.start_date"
                            type="date"
                        />
                    </div>
                    <div class="col-md-3">
                        <InputField
                            id="end_date"
                            label="Data Final"
                            v-model="searchForm.end_date"
                            type="date"
                        />
                    </div>

                    <div class="col-md-6 d-flex justify-content-end">
                        <button
                            class="btn btn-secondary mt-auto mb-3 mr-2"
                            @click="resetFilter"
                        >
                            <i class="fas fa-times"></i>
                            &nbsp; Limpar Filtros
                        </button>
                        <button
                            class="btn btn-primary mt-auto mb-3"
                            @click="applyDateFilter"
                        >
                            <i class="fas fa-search"></i>
                            &nbsp; Filtrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Transferências</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table
                        class="table table-bordered table-striped table-hover"
                    >
                        <thead>
                            <tr>
                                <th class="col-1">Código</th>
                                <th class="col-2">Data</th>
                                <th class="col-2">De</th>
                                <th class="col-2">Para</th>
                                <th class="col-2">Valor</th>
                                <th class="col-2">Observações</th>
                                <th class="col-1">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="transfer in transfers.data"
                                :key="transfer.id"
                            >
                                <td>
                                    {{
                                        formatSequentialId(
                                            transfer.sequential_id
                                        )
                                    }}
                                </td>
                                <td>
                                    {{ formatDate(transfer.transfer_date) }}
                                </td>
                                <td>
                                    {{ transfer.source_account.name }}
                                </td>
                                <td>
                                    {{ transfer.destination_account.name }}
                                </td>
                                <td>
                                    {{ formatCurrency(transfer.amount) }}
                                </td>
                                <td>
                                    {{ transfer.notes }}
                                </td>
                                <td>
                                    <div class="text-nowrap">
                                        <Link
                                            :href="
                                                route(
                                                    'account-transfers.show',
                                                    transfer.id
                                                )
                                            "
                                            class="btn btn-sm btn-secondary mr-1"
                                        >
                                            Visualizar
                                        </Link>
                                        <button
                                            class="btn btn-sm btn-danger"
                                            @click="confirmDelete(transfer)"
                                        >
                                            Excluir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="transfers.data.length === 0">
                                <td colspan="7" class="text-center">
                                    Nenhum registro encontrado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <Pagination :links="transfers.links" />
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmation
            v-if="showDeleteModal"
            :visible="showDeleteModal"
            :loading="loading"
            title="Confirmar Exclusão"
            message="Você tem certeza que deseja excluir esta transferência? Esta ação irá reverter os valores nas contas envolvidas."
            warning="Esta ação não pode ser desfeita."
            delete-route-method="delete"
            :delete-route="handleDelete"
            @cancel="cancelDelete"
            success-redirect="account-transfers.index"
            success-message="Transferência excluída com sucesso!"
        />
    </AppLayout>
</template>
