<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import GroupForm from "@/Pages/Groups/GroupForm.vue";
import { ref } from "vue";

const formRef = ref(null);

const handleSubmit = (form) => {
    form.post(route("groups.store"));
};
</script>

<template>
    <Head title="Criar Grupos" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Criar Grupos</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Seções', routeName: 'groups.index' },
                        { label: 'Criar' },
                    ]"
                />
            </div>
            <Link
                :href="route('groups.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>
        <div class="card">
            <div class="card-header">Cadastro de Grupos</div>
            <div class="card-body">
                <GroupForm ref="formRef" @submit="handleSubmit" />
            </div>
        </div>
    </AppLayout>
</template>
