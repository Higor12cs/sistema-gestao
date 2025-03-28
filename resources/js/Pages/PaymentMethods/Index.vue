<script setup>
import { ref, watch, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router, Link } from "@inertiajs/vue3";
import { debounce } from "lodash";
import Pagination from "@/Components/Pagination.vue";
import DeleteConfirmation from "@/Components/DeleteConfirmation.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";

const props = defineProps({
    paymentMethods: Object,
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
            route("payment-methods.index"),
            { search: value },
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300)
);

const confirmDelete = (paymentMethod) => {
    deleteUserId.value = paymentMethod.id;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    loading.value = true;
    return route("payment-methods.destroy", deleteUserId.value);
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    deleteUserId.value = null;
};
</script>

<template>
    <Head title="Métodos de Pagamento" />
    <AuthenticatedLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Métodos de Pagamento</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Métodos de Pagamento' },
                    ]"
                />
            </div>

            <Link
                :href="route('payment-methods.create')"
                class="btn btn-primary mb-auto"
            >
                <i class="fas fa-sm fa-plus"></i>
                &nbsp; Nova Forma
            </Link>
        </div>

        <div class="card">
            <div class="card-header">Métodos de Pagamento</div>
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
                        id="paymentMethodsTable"
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
                                v-for="paymentMethod in paymentMethods.data"
                                :key="paymentMethod.id"
                            >
                                <td>
                                    {{
                                        String(
                                            paymentMethod.sequential_id
                                        ).padStart(6, "0")
                                    }}
                                </td>
                                <td>{{ paymentMethod.name }}</td>
                                <td>
                                    <span
                                        v-if="paymentMethod.active"
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
                                                    'payment-methods.edit',
                                                    paymentMethod.sequential_id
                                                )
                                            "
                                            class="btn btn-sm btn-secondary mr-1"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            class="btn btn-sm btn-danger"
                                            @click="
                                                confirmDelete(paymentMethod)
                                            "
                                        >
                                            Excluir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="paymentMethods.data.length === 0">
                                <td colspan="4" class="text-center">
                                    Nenhum registro encontrado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <Pagination :links="paymentMethods.links" />
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmation
            v-if="showDeleteModal"
            :visible="showDeleteModal"
            :loading="loading"
            title="Confirmar Exclusão"
            message="Você tem certeza que deseja excluir esta forma de pagamento?"
            warning="Esta ação não pode ser desfeita."
            delete-route-method="delete"
            :delete-route="handleDelete"
            @cancel="cancelDelete"
            success-redirect="paymentMethods.index"
            success-message="Método de Pagamento excluída com sucesso!"
        />
    </AuthenticatedLayout>
</template>
