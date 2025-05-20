<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import ChartAccountForm from "@/Pages/ChartAccounts/ChartAccountForm.vue";
import { ref } from "vue";

const props = defineProps({
    account: Object,
    potentialParents: Array,
    hasChildren: Boolean,
});

const formRef = ref(null);

const handleSubmit = (form) => {
    form.post(route("chart-accounts.update", props.account.id));
};
</script>

<template>
    <Head title="Editar Plano de Conta" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Editar Plano de Conta</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        {
                            label: 'Planos de Contas',
                            routeName: 'chart-accounts.index',
                        },
                        { label: 'Editar' },
                    ]"
                />
            </div>
            <Link
                :href="
                    route('chart-accounts.index', {
                        parent_id: account.parent_id,
                    })
                "
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between">
                    <span>Edição de Plano de Conta</span>
                    <span class="badge badge-info">{{ account.code }}</span>
                </div>
            </div>
            <div class="card-body">
                <ChartAccountForm
                    ref="formRef"
                    :account="account"
                    :potential-parents="potentialParents"
                    :has-children="hasChildren"
                    :processing="formRef?.form?.processing"
                    @submit="handleSubmit"
                />
            </div>
        </div>
    </AppLayout>
</template>
