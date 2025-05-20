<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import SupplierForm from "@/Pages/Suppliers/SupplierForm.vue";
import { ref } from "vue";

const props = defineProps({
    supplier: Object,
});

const formRef = ref(null);

const handleSubmit = (form) => {
    form.post(route("suppliers.update", props.supplier.id));
};
</script>

<template>
    <Head title="Editar Fornecedor" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Editar Fornecedor</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Fornecedores', routeName: 'suppliers.index' },
                        { label: 'Editar' },
                    ]"
                />
            </div>

            <Link
                :href="route('suppliers.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>

        <div class="card">
            <div class="card-header">Edição de Fornecedor</div>
            <div class="card-body">
                <SupplierForm
                    ref="formRef"
                    :supplier="supplier"
                    :processing="formRef?.form?.processing"
                    @submit="handleSubmit"
                />
            </div>
        </div>
    </AppLayout>
</template>
