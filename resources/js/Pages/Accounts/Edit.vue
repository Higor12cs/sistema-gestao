<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import AccountForm from "@/Pages/Accounts/AccountForm.vue";
import { ref } from "vue";

const props = defineProps({
    account: Object,
});

const formRef = ref(null);

const handleSubmit = (form) => {
    form.post(route("accounts.update", props.account.id));
};
</script>

<template>
    <Head title="Editar Conta" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Editar Conta</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Contas', routeName: 'accounts.index' },
                        { label: 'Editar' },
                    ]"
                />
            </div>
            <Link
                :href="route('accounts.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>
        <div class="card">
            <div class="card-header">Edição de Conta</div>
            <div class="card-body">
                <AccountForm
                    ref="formRef"
                    :account="account"
                    :processing="formRef?.form?.processing"
                    @submit="handleSubmit"
                />
            </div>
        </div>
    </AppLayout>
</template>
