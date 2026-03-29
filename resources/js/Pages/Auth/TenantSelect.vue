<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    tenants: Array,
});

const appName = import.meta.env.VITE_APP_NAME ?? "AdminApp";
</script>

<template>
    <Head title="Selecionar Empresa" />

    <div class="hold-transition login-page">
        <div class="login-box">
            <div class="card card-outline card-primary">
                <div class="card-header text-center">
                    <Link href="/" class="h3">
                        {{ appName }}
                    </Link>
                </div>

                <div class="card-body">
                    <p class="login-box-msg">
                        Escolha o ambiente que deseja acessar:
                    </p>

                    <div v-if="tenants && tenants.length > 0" class="row mb-3">
                        <Link
                            v-for="tenant in tenants"
                            :key="tenant.id"
                            :href="route('tenant-select.store')"
                            method="post"
                            :data="{ tenant_id: tenant.id }"
                            as="button"
                            class="btn btn-lg btn-light col-6"
                        >
                            <i class="fas fa-building mr-2"></i>
                            <span class="tenant-name">{{ tenant.name }}</span>
                        </Link>
                    </div>

                    <div v-else class="tenant-empty mb-3">
                        Você não possui vínculo com nenhuma empresa.
                    </div>

                    <div class="text-center">
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="btn btn-sm btn-danger"
                        >
                            Sair da conta
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
