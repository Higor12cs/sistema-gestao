<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import BrandForm from "@/Pages/Brands/BrandForm.vue";
import { ref } from "vue";

const props = defineProps({
    brand: Object,
});

const formRef = ref(null);

const handleSubmit = (form) => {
    form.post(route("brands.update", props.brand.id));
};
</script>

<template>
    <Head title="Editar Marca" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Editar Marca</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Marcas', routeName: 'brands.index' },
                        { label: 'Editar' },
                    ]"
                />
            </div>
            <Link
                :href="route('brands.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>
        <div class="card">
            <div class="card-header">Edição de Marca</div>
            <div class="card-body">
                <BrandForm
                    ref="formRef"
                    :brand="brand"
                    :processing="formRef?.form?.processing"
                    @submit="handleSubmit"
                />
            </div>
        </div>
    </AppLayout>
</template>
