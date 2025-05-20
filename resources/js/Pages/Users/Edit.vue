<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Select2 from "@/Components/Select2.vue";

const props = defineProps({
    user: Object,
    userRoleId: Number,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role_id: props.userRoleId,
});

const submit = () => {
    form.put(route("users.update", props.user.id));
};
</script>

<template>
    <Head title="Editar Usuário" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Editar Usuário</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Usuários', routeName: 'users.index' },
                        { label: 'Editar' },
                    ]"
                />
            </div>
            <Link
                :href="route('users.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>&nbsp; Voltar
            </Link>
        </div>
        <div class="card">
            <div class="card-header">Edição de Usuário</div>
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="form-group">
                        <label for="name">Nome</label>
                        <input
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.name }"
                            id="name"
                            v-model="form.name"
                        />
                        <div v-if="form.errors.name" class="invalid-feedback">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input
                            type="email"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.email }"
                            id="email"
                            v-model="form.email"
                        />
                        <div v-if="form.errors.email" class="invalid-feedback">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <div class="form-group">
                        <Select2
                            label="Papel"
                            v-model="form.role_id"
                            :error="form.errors.role_id"
                            :search-url="route('api.roles.search')"
                            value-key="id"
                            label-key="name"
                            placeholder="Pesquisar"
                        />
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button
                            type="submit"
                            class="btn btn-primary"
                            :disabled="form.processing"
                        >
                            <span
                                v-if="form.processing"
                                class="spinner-border spinner-border-sm mr-2"
                                role="status"
                                aria-hidden="true"
                            ></span>
                            <span v-if="form.processing">Salvando...</span>
                            <span v-else>
                                <i class="fas fa-save"></i>
                                &nbsp; Salvar
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
