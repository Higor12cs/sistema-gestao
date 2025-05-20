<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import SellerForm from "@/Pages/Sellers/SellerForm.vue";
import { ref } from "vue";

const props = defineProps({
    seller: Object,
});

const formRef = ref(null);

const handleSubmit = (form) => {
    form.post(route("sellers.update", props.seller.id));
};
</script>

<template>
    <Head title="Editar Vendedor" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Editar Vendedor</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Vendedores', routeName: 'sellers.index' },
                        { label: 'Editar' },
                    ]"
                />
            </div>
            <Link
                :href="route('sellers.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>
        <div class="card">
            <div class="card-header">Edição de Vendedor</div>
            <div class="card-body">
                <SellerForm
                    ref="formRef"
                    :seller="seller"
                    :processing="formRef?.form?.processing"
                    @submit="handleSubmit"
                />
            </div>
        </div>
    </AppLayout>
</template>
