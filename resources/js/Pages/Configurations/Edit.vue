<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import ConfigurationForm from "@/Pages/Configurations/ConfigurationForm.vue";
import { ref } from "vue";

const props = defineProps({
    configuration: Object,
});

const formRef = ref(null);

const handleSubmit = (form) => {
    form.post(route("configurations.update", props.configuration.id));
};
</script>

<template>
    <Head title="Editar Configuração" />
    <AuthenticatedLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Editar Configuração</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Configuraçãos', routeName: 'configurations.index' },
                        { label: 'Editar' },
                    ]"
                />
            </div>
            <Link
                :href="route('configurations.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>
        <div class="card">
            <div class="card-header">Edição da Configuração</div>
            <div class="card-body">
                <ConfigurationForm
                    ref="formRef"
                    :configuration="configuration"
                    :processing="formRef?.form?.processing"
                    @submit="handleSubmit"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
