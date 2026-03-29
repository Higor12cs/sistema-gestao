<script setup>
import { ref, watch } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, useForm, router, Link } from "@inertiajs/vue3";
import { debounce } from "lodash";
import Pagination from "@/Components/Pagination.vue";
import DeleteConfirmation from "@/Components/DeleteConfirmation.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";

const props = defineProps({
    roles: Object,
    filters: Object,
});

const searchForm = useForm({
    search: props.filters?.search || "",
});

const loading = ref(false);
const showDeleteModal = ref(false);
const deleteRoleId = ref(null);

watch(
    () => searchForm.search,
    debounce(function (value) {
        router.get(
            route("roles.index"),
            { search: value },
            {
                preserveState: true,
                replace: true,
            },
        );
    }, 300),
);

const confirmDelete = (role) => {
    if (role.name === "Administrador") {
        alert("Não é possível excluir o papel de Administrador");
        return;
    }
    deleteRoleId.value = role.id;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    loading.value = true;
    return route("roles.destroy", deleteRoleId.value);
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    deleteRoleId.value = null;
};

const getPermissionBadges = (permissions) => {
    let badges = "";
    const permissionsToShow = permissions.slice(0, 5);

    permissionsToShow.forEach((permission) => {
        badges += `<span class="badge badge-info">${permission.description}</span> `;
    });

    if (permissions.length > 5) {
        badges += `<span class="badge badge-secondary" data-toggle="tooltip" title="${
            permissions.length - 5
        } permissões adicionais">+${permissions.length - 5}</span>`;
    }

    return badges;
};
</script>

<template>
    <Head title="Papéis" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Papéis</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Papéis' },
                    ]"
                />
            </div>

            <Link :href="route('roles.create')" class="btn btn-primary mb-auto">
                <i class="fas fa-sm fa-plus"></i>
                &nbsp; Novo Papel
            </Link>
        </div>

        <div class="card">
            <div class="card-header">Lista de Papéis</div>
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
                        id="rolesTable"
                        class="table table-bordered table-striped table-hover"
                    >
                        <thead>
                            <tr>
                                <th class="col-1">Código</th>
                                <th class="col-5">Nome</th>
                                <th class="col-5">Papéis</th>
                                <th class="col-1">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="role in roles.data" :key="role.id">
                                <td>
                                    {{ String(role.id).padStart(6, "0") }}
                                </td>
                                <td>{{ role.name }}</td>
                                <td>
                                    <div class="permission-badges">
                                        <span
                                            v-for="(
                                                permission, index
                                            ) in role.permissions.slice(0, 5)"
                                            :key="permission.id"
                                            class="badge badge-info mr-1"
                                        >
                                            {{ permission.description }}
                                        </span>
                                        <span
                                            v-if="role.permissions.length > 5"
                                            class="badge badge-secondary permissions-counter"
                                            data-toggle="tooltip"
                                            :title="`${
                                                role.permissions.length - 5
                                            } permissões adicionais`"
                                        >
                                            +{{ role.permissions.length - 5 }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div
                                        v-if="role.name !== 'Administrador'"
                                        class="text-nowrap"
                                    >
                                        <Link
                                            :href="route('roles.edit', role.id)"
                                            class="btn btn-sm btn-secondary mr-1"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            class="btn btn-sm btn-danger"
                                            @click="confirmDelete(role)"
                                        >
                                            Excluir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="roles.data.length === 0">
                                <td colspan="3" class="text-center">
                                    Nenhum registro encontrado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <Pagination :links="roles.links" />
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmation
            v-if="showDeleteModal"
            :visible="showDeleteModal"
            :loading="loading"
            title="Confirmar Exclusão"
            message="Você tem certeza que deseja excluir este papel?"
            warning="Esta ação não pode ser desfeita."
            delete-route-method="delete"
            :delete-route="handleDelete"
            @cancel="cancelDelete"
            success-redirect="roles.index"
            success-message="Papel excluído com sucesso!"
        />
    </AppLayout>
</template>

<style scoped>
.permission-badges {
    max-width: 600px;
}
.badge {
    margin-right: 3px;
    margin-bottom: 3px;
}
</style>
