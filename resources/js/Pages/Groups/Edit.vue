<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import GroupForm from "@/Pages/Groups/GroupForm.vue";
import { ref } from "vue";

const props = defineProps({
    group: Object,
});

const formRef = ref(null);

const handleSubmit = (form) => {
    form.post(route("groups.update", props.group.id));
};
</script>

<template>
    <Head title="Editar Grupo" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Editar Grupo</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Grupo', routeName: 'groups.index' },
                        { label: 'Editar' },
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
            <div class="card-header">Edição de Grupo</div>
            <div class="card-body">
                <GroupForm
                    ref="formRef"
                    :group="group"
                    :processing="formRef?.form?.processing"
                    @submit="handleSubmit"
                />
            </div>
        </div>
    </AppLayout>
</template>
