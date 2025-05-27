<script setup>
import { ref, watch, onMounted } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, useForm, router, Link } from "@inertiajs/vue3";
import { debounce } from "lodash";
import Pagination from "@/Components/Pagination.vue";
import DeleteConfirmation from "@/Components/DeleteConfirmation.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";

const props = defineProps({
    groups: Object,
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
            route("groups.index"),
            { search: value },
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300)
);

const confirmDelete = (group) => {
    deleteUserId.value = group.id;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    loading.value = true;
    return route("groups.destroy", deleteUserId.value);
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    deleteUserId.value = null;
};
</script>

<template>
    <Head title="Grupos" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Grupos</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Grupos' },
                    ]"
                />
            </div>

            <Link
                :href="route('groups.create')"
                class="btn btn-primary mb-auto"
            >
                <i class="fas fa-sm fa-plus"></i>
                &nbsp; Novo Grupo
            </Link>
        </div>

        <div class="card">
            <div class="card-header">Grupos</div>
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
                        id="groupsTable"
                        class="table table-bordered table-striped table-hover"
                    >
                        <thead>
                            <tr>
                                <th class="col-1">Código</th>
                                <th class="col-5">Nome</th>
                                <th class="col-4">Seção</th>
                                <th class="col-1">Ativo</th>
                                <th class="col-1">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="group in groups.data" :key="group.id">
                                <td>
                                    {{
                                        String(group.sequential_id).padStart(
                                            6,
                                            "0"
                                        )
                                    }}
                                </td>
                                <td>{{ group.name }}</td>
                                <td>{{ group.section.name }}</td>
                                <td>
                                    <span
                                        :class="{
                                            'badge badge-success': group.active,
                                            'badge badge-danger': !group.active,
                                        }"
                                    >
                                        {{ group.active ? "Ativo" : "Inativo" }}
                                    </span>
                                </td>
                                <td>
                                    <div class="text-nowrap">
                                        <Link
                                            :href="
                                                route(
                                                    'groups.edit',
                                                    group.id
                                                )
                                            "
                                            class="btn btn-sm btn-secondary mr-1"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            class="btn btn-sm btn-danger"
                                            @click="confirmDelete(group)"
                                        >
                                            Excluir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="groups.data.length === 0">
                                <td colspan="5" class="text-center">
                                    Nenhum registro encontrado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <Pagination :links="groups.links" />
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmation
            v-if="showDeleteModal"
            :visible="showDeleteModal"
            :loading="loading"
            title="Confirmar Exclusão"
            message="Você tem certeza que deseja excluir este grupo?"
            warning="Esta ação não pode ser desfeita."
            delete-route-method="delete"
            :delete-route="handleDelete"
            @cancel="cancelDelete"
            success-redirect="groups.index"
            success-message="Grupo excluído com sucesso!"
        />
    </AppLayout>
</template>
