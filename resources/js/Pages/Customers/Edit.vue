<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import CustomerForm from "@/Pages/Customers/CustomerForm.vue";
import { ref } from "vue";

const props = defineProps({
    customer: Object,
});

const formRef = ref(null);

const handleSubmit = (form) => {
    form.post(route("customers.update", props.customer.id));
};
</script>

<template>
    <Head title="Editar Cliente" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Editar Cliente</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Clientes', routeName: 'customers.index' },
                        { label: 'Editar' },
                    ]"
                />
            </div>

            <Link
                :href="route('customers.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>

        <div class="card">
            <div class="card-header">Edição de Cliente</div>
            <div class="card-body">
                <CustomerForm
                    ref="formRef"
                    :customer="customer"
                    :processing="formRef?.form?.processing"
                    @submit="handleSubmit"
                />
            </div>
        </div>
    </AppLayout>
</template>
