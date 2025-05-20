<script setup>
import { ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Select2 from "@/Components/Select2.vue";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    role_id: null,
});

const submit = () => {
    form.post(route("users.store"));
};
</script>

<template>
    <Head title="Criar Usuário" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Criar Usuário</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Usuários', routeName: 'users.index' },
                        { label: 'Criar' },
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
            <div class="card-header">Cadastro de Usuário</div>
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

                    <div class="form-group">
                        <label for="password">Senha</label>
                        <input
                            type="password"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.password }"
                            id="password"
                            v-model="form.password"
                        />
                        <div
                            v-if="form.errors.password"
                            class="invalid-feedback"
                        >
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation"
                            >Confirmação Senha</label
                        >
                        <input
                            type="password"
                            class="form-control"
                            :class="{
                                'is-invalid': form.errors.password_confirmation,
                            }"
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                        />
                        <div
                            v-if="form.errors.password_confirmation"
                            class="invalid-feedback"
                        >
                            {{ form.errors.password_confirmation }}
                        </div>
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
