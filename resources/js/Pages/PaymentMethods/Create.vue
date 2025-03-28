<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import PaymentMethodForm from "@/Pages/PaymentMethods/PaymentMethodForm.vue";
import { ref } from "vue";

const formRef = ref(null);

const handleSubmit = (form) => {
    form.post(route("payment-methods.store"));
};
</script>

<template>
    <Head title="Criar Método de Pagamento" />
    <AuthenticatedLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Criar Método de Pagamento</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Métodos de Pagamento', routeName: 'payment-methods.index' },
                        { label: 'Criar' },
                    ]"
                />
            </div>
            <Link
                :href="route('payment-methods.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>
        <div class="card">
            <div class="card-header">Cadastro de Método de Pagamento</div>
            <div class="card-body">
                <PaymentMethodForm
                    ref="formRef"
                    :processing="formRef?.form?.processing"
                    @submit="handleSubmit"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
