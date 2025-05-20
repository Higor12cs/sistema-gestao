<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import SectionForm from "@/Pages/Sections/SectionForm.vue";
import { ref } from "vue";

const props = defineProps({
    section: Object,
});

const formRef = ref(null);

const handleSubmit = (form) => {
    form.post(route("sections.update", props.section.id));
};
</script>

<template>
    <Head title="Editar Seção" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Editar Seção</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Seções', routeName: 'sections.index' },
                        { label: 'Editar' },
                    ]"
                />
            </div>
            <Link
                :href="route('sections.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>
        <div class="card">
            <div class="card-header">Edição de Seção</div>
            <div class="card-body">
                <SectionForm
                    ref="formRef"
                    :section="section"
                    :processing="formRef?.form?.processing"
                    @submit="handleSubmit"
                />
            </div>
        </div>
    </AppLayout>
</template>
