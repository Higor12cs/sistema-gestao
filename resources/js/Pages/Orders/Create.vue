<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import OrderForm from "@/Pages/Orders/OrderForm.vue";
import { ref } from "vue";

const formRef = ref(null);

const handleSubmit = (form) => {
    form.post(route("orders.store"));
};
</script>

<template>
    <Head title="Criar Pedido" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Criar Pedido</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Pedidos', routeName: 'orders.index' },
                        { label: 'Criar' },
                    ]"
                />
            </div>
            <Link
                :href="route('orders.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>
        <div class="card">
            <div class="card-header">Cadastro de Pedido</div>
            <div class="card-body">
                <OrderForm ref="formRef" @submit="handleSubmit" />
            </div>
        </div>
    </AppLayout>
</template>
