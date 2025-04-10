<script setup>
import { ref, watch, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router, Link } from "@inertiajs/vue3";
import { debounce } from "lodash";
import Pagination from "@/Components/Pagination.vue";
import DeleteConfirmation from "@/Components/DeleteConfirmation.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { formatCurrency } from "@/utils";

const props = defineProps({
    products: Object,
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
            route("products.index"),
            { search: value },
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300)
);

const confirmDelete = (product) => {
    deleteUserId.value = product.id;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    loading.value = true;
    return route("products.destroy", deleteUserId.value);
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    deleteUserId.value = null;
};
</script>

<template>
    <Head title="Produtos" />
    <AuthenticatedLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Produtos</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Produtos' },
                    ]"
                />
            </div>

            <Link
                :href="route('products.create')"
                class="btn btn-primary mb-auto"
            >
                <i class="fas fa-sm fa-plus"></i>
                &nbsp; Novo Produto
            </Link>
        </div>

        <div class="card">
            <div class="card-header">Produtos</div>
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
                        id="productsTable"
                        class="table table-bordered table-striped table-hover"
                    >
                        <thead>
                            <tr>
                                <th class="col-1">Código</th>
                                <th class="col-5">Nome</th>
                                <th class="col-1">Seção</th>
                                <th class="col-1">Grupo</th>
                                <th class="col-1">Valor</th>
                                <th class="col-1">Ativo</th>
                                <th class="col-1">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="product in products.data"
                                :key="product.id"
                            >
                                <td>
                                    {{
                                        String(product.sequential_id).padStart(
                                            6,
                                            "0"
                                        )
                                    }}
                                </td>
                                <td>{{ product.name }}</td>
                                <td>{{ product?.group?.section?.name }}</td>
                                <td>{{ product?.group?.name }}</td>
                                <td>{{ formatCurrency(product.price) }}</td>
                                <td>
                                    <span
                                        class="badge"
                                        :class="
                                            product.active
                                                ? 'bg-success'
                                                : 'bg-danger'
                                        "
                                    >
                                        {{ product.active ? "Sim" : "Não" }}
                                    </span>
                                </td>
                                <td>
                                    <div class="text-nowrap">
                                        <Link
                                            :href="
                                                route(
                                                    'products.edit',
                                                    product.sequential_id
                                                )
                                            "
                                            class="btn btn-sm btn-secondary mr-1"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            class="btn btn-sm btn-danger"
                                            @click="confirmDelete(product)"
                                        >
                                            Excluir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="products.data.length === 0">
                                <td colspan="7" class="text-center">
                                    Nenhum registro encontrado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <Pagination :links="products.links" />
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmation
            v-if="showDeleteModal"
            :visible="showDeleteModal"
            :loading="loading"
            title="Confirmar Exclusão"
            message="Você tem certeza que deseja excluir este produto?"
            warning="Esta ação não pode ser desfeita."
            delete-route-method="delete"
            :delete-route="handleDelete"
            @cancel="cancelDelete"
            success-redirect="products.index"
            success-message="Produto excluído com sucesso!"
        />
    </AuthenticatedLayout>
</template>
