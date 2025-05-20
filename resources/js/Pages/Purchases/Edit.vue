<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import PurchaseForm from "@/Pages/Purchases/PurchaseForm.vue";
import { ref } from "vue";

const props = defineProps({
    purchase: Object,
});

const formRef = ref(null);

const handleSubmit = (form) => {
    form.put(route("purchases.update", props.purchase.id));
};
</script>

<template>
    <Head title="Editar Compra" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Editar Compra</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Compras', routeName: 'purchases.index' },
                        { label: 'Editar' },
                    ]"
                />
            </div>
            <Link
                :href="route('purchases.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>
        <div class="card">
            <div class="card-header">Edição de Compra</div>
            <div class="card-body">
                <PurchaseForm
                    ref="formRef"
                    :purchase="purchase"
                    @submit="handleSubmit"
                />
            </div>
        </div>
    </AppLayout>
</template>
