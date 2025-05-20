<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import ChartAccountForm from "@/Pages/ChartAccounts/ChartAccountForm.vue";
import { ref } from "vue";

const props = defineProps({
    potentialParents: Array,
    preselectedParentId: String,
});

const formRef = ref(null);

const handleSubmit = (form) => {
    form.post(route("chart-accounts.store"));
};
</script>

<template>
    <Head title="Criar Plano de Conta" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Criar Plano de Conta</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        {
                            label: 'Planos de Contas',
                            routeName: 'chart-accounts.index',
                        },
                        { label: 'Criar' },
                    ]"
                />
            </div>
            <Link
                :href="
                    route('chart-accounts.index', {
                        parent_id: preselectedParentId,
                    })
                "
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>
        <div class="card">
            <div class="card-header">Cadastro de Plano de Conta</div>
            <div class="card-body">
                <ChartAccountForm
                    ref="formRef"
                    :potential-parents="potentialParents"
                    :preselected-parent-id="preselectedParentId"
                    :processing="formRef?.form?.processing"
                    @submit="handleSubmit"
                />
            </div>
        </div>
    </AppLayout>
</template>
