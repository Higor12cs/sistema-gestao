<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import ProductForm from "./ProductForm.vue";
import { ref } from "vue";

const props = defineProps({
    product: Object,
});

const formRef = ref(null);

const handleSubmit = (form) => {
    form.put(route("products.update", props.product.id));
};
</script>

<template>
    <Head title="Editar Produto" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Editar Produto</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Produtos', routeName: 'products.index' },
                        { label: 'Editar' },
                    ]"
                />
            </div>
            <Link
                :href="route('products.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>
        <div class="card">
            <div class="card-header">Edição de Produto</div>
            <div class="card-body">
                <ProductForm
                    ref="formRef"
                    :product="product"
                    :processing="formRef?.form?.processing"
                    @submit="handleSubmit"
                />
            </div>
        </div>
    </AppLayout>
</template>
