<script setup>
import { ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const appName = import.meta.env.VITE_APP_NAME || "";

const form = useForm({
    email: "test@example.com",
    password: "password",
    remember: true,
});

const processing = ref(false);

const submit = () => {
    processing.value = true;
    form.post(route("login.attempt"), {
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>

<template>
    <Head title="Entrar" />

    <div class="hold-transition login-page">
        <div class="login-box">
            <div class="card card-outline card-primary">
                <div class="card-header text-center">
                    <Link href="/" class="h3">
                        {{ appName }}
                    </Link>
                </div>
                <div class="card-body">
                    <p class="login-box-msg">Entre para iniciar sua sessão</p>

                    <form @submit.prevent="submit">
                        <div class="input-group mb-3">
                            <input
                                id="email"
                                type="email"
                                class="form-control"
                                :class="{ 'is-invalid': form.errors.email }"
                                placeholder="Email"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                            />
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                            <div
                                v-if="form.errors.email"
                                class="invalid-feedback"
                            >
                                {{ form.errors.email }}
                            </div>
                        </div>

                        <div class="input-group mb-3">
                            <input
                                id="password"
                                type="password"
                                class="form-control"
                                placeholder="Senha"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                            />
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                            <div
                                v-if="form.errors.password"
                                class="invalid-feedback"
                            >
                                {{ form.errors.password }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-8">
                                <div class="icheck-primary">
                                    <input
                                        id="remember"
                                        type="checkbox"
                                        v-model="form.remember"
                                    />
                                    <label for="remember"
                                        >Permanecer Conectado</label
                                    >
                                </div>
                            </div>

                            <div class="col-4">
                                <button
                                    type="submit"
                                    class="btn btn-primary btn-block"
                                    :disabled="processing"
                                    style="border-radius: 0"
                                >
                                    <span
                                        v-if="processing"
                                        class="spinner-border spinner-border-sm mr-2"
                                        role="status"
                                        aria-hidden="true"
                                    ></span>
                                    <span v-else>Entrar</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    <p class="mb-1">
                        <Link href="#" class="text-primary"
                            >Esqueci minha senha</Link
                        >
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
