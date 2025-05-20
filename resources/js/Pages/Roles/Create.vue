<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import RoleForm from "@/Pages/Roles/RoleForm.vue";
import { ref } from "vue";

const props = defineProps({
    permissions: Array,
});

const formRef = ref(null);

const handleSubmit = (form) => {
    form.post(route("roles.store"));
};
</script>

<template>
    <Head title="Novo Papel" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Nova Permissão</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Permissões', routeName: 'roles.index' },
                        { label: 'Nova' },
                    ]"
                />
            </div>
            <Link
                :href="route('roles.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>
        <div class="card">
            <div class="card-header">Nova Permissão</div>
            <div class="card-body">
                <RoleForm
                    ref="formRef"
                    :permissions="permissions"
                    :processing="formRef?.form?.processing"
                    @submit="handleSubmit"
                />
            </div>
        </div>
    </AppLayout>
</template>
