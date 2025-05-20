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
    parents: Array,
    currentParentId: String,
    filters: Object,
});

const searchForm = useForm({
    search: props.filters?.search || "",
    parent_id: props.filters?.parent_id || null,
});

const loading = ref(false);
const showDeleteModal = ref(false);
const deleteAccountId = ref(null);

watch(
    () => searchForm.search,
    debounce(function (value) {
        router.get(
            route("chart-accounts.index"),
            { search: value, parent_id: searchForm.parent_id },
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300)
);

const confirmDelete = (account) => {
    deleteAccountId.value = account.id;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    loading.value = true;
    return route("chart-accounts.destroy", deleteAccountId.value);
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    deleteAccountId.value = null;
};

const navigateToParent = (parentId = null) => {
    router.get(
        route("chart-accounts.index"),
        { parent_id: parentId },
        {
            preserveState: true,
        }
    );
};

const navigateToChildren = (parentId) => {
    router.get(
        route("chart-accounts.index"),
        { parent_id: parentId },
        {
            preserveState: true,
        }
    );
};

const createWithParent = (parentId = null) => {
    const params = {};
    if (parentId) {
        params.parent_id = parentId;
    }
    router.get(route("chart-accounts.create", params));
};
</script>

<template>
    <Head title="Planos de Contas" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Planos de Contas</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Planos de Contas' },
                    ]"
                />
            </div>

            <div>
                <button
                    v-if="currentParentId"
                    @click="createWithParent(currentParentId)"
                    class="btn btn-secondary mr-2"
                >
                    <i class="fas fa-sm fa-plus"></i>
                    &nbsp; Novo Filho
                </button>
                <button @click="createWithParent()" class="btn btn-primary">
                    <i class="fas fa-sm fa-plus"></i>
                    &nbsp; Novo Plano de Conta
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Planos de Contas</div>
            <div class="card-body">
                <!-- Navegação hierárquica -->
                <div v-if="parents.length > 0" class="mb-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="#" @click.prevent="navigateToParent()"
                                    >Raiz</a
                                >
                            </li>
                            <li
                                v-for="parent in parents"
                                :key="parent.id"
                                class="breadcrumb-item"
                                :class="{
                                    active: parent.id === currentParentId,
                                }"
                            >
                                <a
                                    v-if="parent.id !== currentParentId"
                                    href="#"
                                    @click.prevent="navigateToParent(parent.id)"
                                >
                                    {{ parent.code }} - {{ parent.name }}
                                </a>
                                <span v-else>
                                    {{ parent.code }} - {{ parent.name }}
                                </span>
                            </li>
                        </ol>
                    </nav>
                </div>

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
                        class="table table-bordered table-striped table-hover"
                    >
                        <thead class="text-nowrap">
                            <tr>
                                <th class="col-1">Código</th>
                                <th class="col-6">Nome</th>
                                <th class="col-2">Permite Lançamentos</th>
                                <th class="col-1">Ativo</th>
                                <th class="col-2">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="account in accounts.data"
                                :key="account.id"
                            >
                                <td>
                                    {{ account.code }}
                                </td>
                                <td>{{ account.name }}</td>
                                <td>
                                    <span
                                        v-if="account.allows_transactions"
                                        class="badge badge-success"
                                    >
                                        Sim
                                    </span>
                                    <span v-else class="badge badge-secondary">
                                        Não
                                    </span>
                                </td>
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
                                        <button
                                            class="btn btn-sm btn-secondary mr-1"
                                            @click="
                                                navigateToChildren(account.id)
                                            "
                                        >
                                            Filhos
                                        </button>
                                        <Link
                                            :href="
                                                route(
                                                    'chart-accounts.edit',
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
                                <td colspan="5" class="text-center">
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
            message="Você tem certeza que deseja excluir este plano de conta?"
            warning="Esta ação não pode ser desfeita. Planos de conta com subcontas não podem ser excluídos."
            delete-route-method="delete"
            :delete-route="handleDelete"
            @cancel="cancelDelete"
            success-redirect="chart-accounts.index"
            success-message="Plano de conta excluído com sucesso!"
        />
    </AppLayout>
</template>
