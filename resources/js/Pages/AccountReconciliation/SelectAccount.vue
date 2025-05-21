<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { formatCurrency } from "../../Utils/Formatters.js";

const props = defineProps({
    accounts: Array,
});
</script>

<template>
    <Head title="Conciliação" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Conciliação</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Contas', routeName: 'accounts.index' },
                        { label: 'Conciliação Bancária' },
                    ]"
                />
            </div>
        </div>

        <div class="card">
            <div class="card-header">Selecione uma Conta</div>
            <div class="card-body">
                <div class="row">
                    <div
                        v-for="account in accounts"
                        :key="account.id"
                        class="col-md-4 mb-4"
                    >
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">{{ account.name }}</h5>
                                <p class="card-text">
                                    <strong>Saldo Atual:</strong>
                                    {{
                                        formatCurrency(account.current_balance)
                                    }}
                                </p>
                                <p class="card-text">
                                    <strong>Transações não conciliadas:</strong>
                                    {{ account.transactions_count }}
                                </p>
                            </div>
                            <div class="card-footer text-center">
                                <Link
                                    :href="
                                        route(
                                            'account-reconciliation.index',
                                            account.sequential_id
                                        )
                                    "
                                    class="btn btn-primary"
                                >
                                    Conciliar Conta
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div v-if="accounts.length === 0" class="col-12">
                        <div class="alert alert-info">
                            Nenhuma conta disponível.
                            <Link
                                :href="route('accounts.create')"
                                class="alert-link"
                                >Cadastre uma conta</Link
                            >
                            para iniciar a conciliação bancária.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
