<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputField from "@/Components/InputField.vue";
import { ref } from "vue";

const props = defineProps({
    payable: Object,
});

const loading = ref(false);

const form = useForm({
    due_date: new Date(props.payable.due_date).toISOString().slice(0, 10),
});

const handleSubmit = () => {
    loading.value = true;
    form.post(route("payables.update", props.payable.id), {
        onSuccess: () => {
            loading.value = false;
        },
        onError: () => {
            loading.value = false;
        },
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("pt-BR", {
        style: "currency",
        currency: "BRL",
    }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("pt-BR", {
        timeZone: "America/Sao_Paulo",
    });
};

const formatSequentialId = (id) => {
    return String(id).padStart(6, "0");
};
</script>

<template>
    <Head title="Editar Pagável" />
    <AuthenticatedLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Editar Pagável</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Pagáveis', routeName: 'payables.index' },
                        { label: 'Editar' },
                    ]"
                />
            </div>
            <Link
                :href="route('payables.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>
        <div class="card">
            <div class="card-header">Edição de Pagável</div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Código</label>
                            <p class="form-control-static">
                                {{ formatSequentialId(payable.sequential_id) }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Fornecedor</label>
                            <p class="form-control-static">
                                {{ payable.supplier.first_name }}
                                {{ " " }}
                                {{ payable.supplier.last_name }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Método de Pagamento</label>
                            <p class="form-control-static">
                                {{ payable.payment_method.name }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Data de Emissão</label>
                            <p class="form-control-static">
                                {{ formatDate(payable.issue_date) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Valor Total</label>
                            <p class="form-control-static">
                                {{ formatCurrency(payable.total_amount) }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Valor Pago</label>
                            <p class="form-control-static">
                                {{ formatCurrency(payable.paid_amount) }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Saldo</label>
                            <p class="form-control-static">
                                {{ formatCurrency(payable.remaining_amount) }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status</label>
                            <p class="form-control-static">
                                <span
                                    :class="[
                                        'badge',
                                        payable.status === 'paid'
                                            ? 'badge-success'
                                            : payable.status === 'partial'
                                            ? 'badge-warning'
                                            : 'badge-secondary',
                                    ]"
                                >
                                    {{
                                        payable.status === "paid"
                                            ? "Pago"
                                            : payable.status === "partial"
                                            ? "Parcial"
                                            : "Pendente"
                                    }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="handleSubmit">
                    <div class="row">
                        <div class="col-md-6">
                            <InputField
                                id="due_date"
                                label="Data de Vencimento"
                                v-model="form.due_date"
                                type="date"
                                :error="form.errors.due_date"
                                required
                            />
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button
                            type="submit"
                            class="btn btn-primary"
                            :disabled="loading"
                        >
                            <span
                                v-if="loading"
                                class="spinner-border spinner-border-sm mr-2"
                                role="status"
                                aria-hidden="true"
                            ></span>
                            <span v-if="loading">Salvando...</span>
                            <span v-else>
                                <i class="fas fa-save"></i>
                                &nbsp; Salvar
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
