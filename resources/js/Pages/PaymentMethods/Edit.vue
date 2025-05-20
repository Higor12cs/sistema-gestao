<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import PaymentMethodForm from "@/Pages/PaymentMethods/PaymentMethodForm.vue";
import { ref } from "vue";

const props = defineProps({
    paymentMethod: Object,
});

const formRef = ref(null);

const handleSubmit = (form) => {
    form.post(route("payment-methods.update", props.paymentMethod.id));
};
</script>

<template>
    <Head title="Editar Método de Pagamento" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Editar Método de Pagamento</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        {
                            label: 'Métodos de Pagamento',
                            routeName: 'payment-methods.index',
                        },
                        { label: 'Editar' },
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
            <div class="card-header">Edição de Método de Pagamento</div>
            <div class="card-body">
                <PaymentMethodForm
                    ref="formRef"
                    :paymentMethod="paymentMethod"
                    :processing="formRef?.form?.processing"
                    @submit="handleSubmit"
                />
            </div>
        </div>
    </AppLayout>
</template>
