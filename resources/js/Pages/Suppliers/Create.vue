<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import SupplierForm from "@/Pages/Suppliers/SupplierForm.vue";
import { ref } from "vue";

const formRef = ref(null);

const handleSubmit = (form) => {
    form.post(route("suppliers.store"));
};
</script>

<template>
    <Head title="Criar Fornecedor" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Criar Fornecedor</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Fornecedores', routeName: 'suppliers.index' },
                        { label: 'Criar' },
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
            <div class="card-header">Cadastro de Fornecedor</div>
            <div class="card-body">
                <SupplierForm
                    ref="formRef"
                    :processing="formRef?.form?.processing"
                    @submit="handleSubmit"
                />
            </div>
        </div>
    </AppLayout>
</template>
