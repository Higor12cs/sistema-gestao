<script setup>
import { ref, watch, onMounted } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, useForm, router, Link } from "@inertiajs/vue3";
import { debounce } from "lodash";
import Pagination from "@/Components/Pagination.vue";
import DeleteConfirmation from "@/Components/DeleteConfirmation.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";

const props = defineProps({
    accounts: Object,
    filters: Object,
});

const searchForm = useForm({
    search: props.filters?.search || "",
});

const loading = ref(false);
const showDeleteModal = ref(false);
const deleteUserId = ref(null);

watch(
    () => searchForm.search,
    debounce(function (value) {
        router.get(
            route("accounts.index"),
            { search: value },
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300)
);

const confirmDelete = (account) => {
    deleteUserId.value = account.id;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    loading.value = true;
    return route("accounts.destroy", deleteUserId.value);
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    deleteUserId.value = null;
};
</script>

<template>
    <Head title="Contas" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Contas</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Contas' },
                    ]"
                />
            </div>

            <Link
                :href="route('accounts.create')"
                class="btn btn-primary mb-auto"
            >
                <i class="fas fa-sm fa-plus"></i>
                &nbsp; Nova Conta
            </Link>
        </div>

        <div class="card">
            <div class="card-header">Contas</div>
            <div class="card-body">
                <!-- Search Box -->
                <div class="mb-3">
                    <div class="input-group">
                        <input
                            type="text"
                            class="form-control"
                            placeholder="Pesquisar"
                            v-model="searchForm.search"
                        />
                        <div class="input-group-append">
                            <button class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table
                        id="accountsTable"
                        class="table table-bordered table-striped table-hover"
                    >
                        <thead>
                            <tr>
                                <th class="col-1">Código</th>
                                <th class="col-9">Nome</th>
                                <th class="col-1">Ativo</th>
                                <th class="col-1">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="account in accounts.data"
                                :key="account.id"
                            >
                                <td>
                                    {{
                                        String(account.sequential_id).padStart(
                                            6,
                                            "0"
                                        )
                                    }}
                                </td>
                                <td>{{ account.name }}</td>
                                <td>
                                    <span
                                        v-if="account.active"
                                        class="badge badge-success"
                                    >
                                        Ativo
                                    </span>
                                    <span v-else class="badge badge-danger">
                                        Inativo
                                    </span>
                                </td>
                                <td>
                                    <div class="text-nowrap">
                                        <Link
                                            :href="
                                                route(
                                                    'accounts.edit',
                                                    account.sequential_id
                                                )
                                            "
                                            class="btn btn-sm btn-secondary mr-1"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            class="btn btn-sm btn-danger"
                                            @click="confirmDelete(account)"
                                        >
                                            Excluir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="accounts.data.length === 0">
                                <td colspan="4" class="text-center">
                                    Nenhum registro encontrado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <Pagination :links="accounts.links" />
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmation
            v-if="showDeleteModal"
            :visible="showDeleteModal"
            :loading="loading"
            title="Confirmar Exclusão"
            message="Você tem certeza que deseja excluir esta conta?"
            warning="Esta ação não pode ser desfeita."
            delete-route-method="delete"
            :delete-route="handleDelete"
            @cancel="cancelDelete"
            success-redirect="accounts.index"
            success-message="Conta excluída com sucesso!"
        />
    </AppLayout>
</template>
