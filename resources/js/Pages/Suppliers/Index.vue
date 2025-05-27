<script setup>
import { ref, watch, onMounted } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, useForm, router, Link } from "@inertiajs/vue3";
import { debounce } from "lodash";
import Pagination from "@/Components/Pagination.vue";
import DeleteConfirmation from "@/Components/DeleteConfirmation.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";

const props = defineProps({
    suppliers: Object,
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
            route("suppliers.index"),
            { search: value },
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300)
);

// onMounted(() => {
//     if (window.$ && window.$.fn.DataTable) {
//         $("#suppliersTable").DataTable({
//             paging: false,
//             searching: false,
//             ordering: true,
//             info: false,
//             responsive: true,
//             language: {
//                 emptyTable: "Nenhum fornecedor encontrado.",
//             },
//         });
//     }
// });

const confirmDelete = (supplier) => {
    deleteUserId.value = supplier.id;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    loading.value = true;
    return route("suppliers.destroy", deleteUserId.value);
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    deleteUserId.value = null;
};
</script>

<template>
    <Head title="Fornecedores" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Fornecedores</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Fornecedores' },
                    ]"
                />
            </div>

            <Link
                :href="route('suppliers.create')"
                class="btn btn-primary mb-auto"
            >
                <i class="fas fa-sm fa-plus"></i>
                &nbsp; Novo Fornecedor
            </Link>
        </div>

        <div class="card">
            <div class="card-header">Fornecedores</div>
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
                        id="suppliersTable"
                        class="table table-bordered table-striped table-hover"
                    >
                        <thead>
                            <tr>
                                <th class="col-1">Código</th>
                                <th class="col-3">Nome</th>
                                <th class="col-3">Sobrenome</th>
                                <th class="col-3">Razão Social</th>
                                <th class="col-1">Ativo</th>
                                <th class="col-1">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="supplier in suppliers.data"
                                :key="supplier.id"
                            >
                                <td>
                                    {{
                                        String(supplier.sequential_id).padStart(
                                            6,
                                            "0"
                                        )
                                    }}
                                </td>
                                <td>{{ supplier.first_name }}</td>
                                <td>{{ supplier.last_name }}</td>
                                <td>{{ supplier.legal_name }}</td>
                                <td>
                                    <span
                                        v-if="supplier.active"
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
                                                    'suppliers.edit',
                                                    supplier.id
                                                )
                                            "
                                            class="btn btn-sm btn-secondary mr-1"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            class="btn btn-sm btn-danger"
                                            @click="confirmDelete(supplier)"
                                        >
                                            Excluir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="suppliers.data.length === 0">
                                <td colspan="6" class="text-center">
                                    Nenhum registro encontrado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <Pagination :links="suppliers.links" />
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmation
            v-if="showDeleteModal"
            :visible="showDeleteModal"
            :loading="loading"
            title="Confirmar Exclusão"
            message="Você tem certeza que deseja excluir este fornecedor?"
            warning="Esta ação não pode ser desfeita."
            delete-route-method="delete"
            :delete-route="handleDelete"
            @cancel="cancelDelete"
            success-redirect="suppliers.index"
            success-message="Fornecedor excluído com sucesso!"
        />
    </AppLayout>
</template>
