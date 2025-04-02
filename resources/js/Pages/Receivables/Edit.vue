<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputField from "@/Components/InputField.vue";
import { ref } from "vue";

const props = defineProps({
    receivable: Object,
});

const loading = ref(false);

const form = useForm({
    due_date: new Date(props.receivable.due_date).toISOString().slice(0, 10),
});

const handleSubmit = () => {
    loading.value = true;
    form.post(route("receivables.update", props.receivable.id), {
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
    if (dateString.includes('T')) {
        dateString = dateString.split('T')[0];
    }

    const [year, month, day] = dateString.split('-');

    return `${day}/${month}/${year}`;
};

const formatSequentialId = (id) => {
    return String(id).padStart(6, "0");
};
</script>

<template>
    <Head title="Editar Recebível" />
    <AuthenticatedLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Editar Recebível</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Recebíveis', routeName: 'receivables.index' },
                        { label: 'Editar' },
                    ]"
                />
            </div>
            <Link
                :href="route('receivables.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>
        <div class="card">
            <div class="card-header">Edição de Recebível</div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Código</label>
                            <p class="form-control-static">
                                {{
                                    formatSequentialId(receivable.sequential_id)
                                }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Cliente</label>
                            <p class="form-control-static">
                                {{ receivable.customer.name }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Método de Pagamento</label>
                            <p class="form-control-static">
                                {{ receivable.payment_method.name }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Data de Emissão</label>
                            <p class="form-control-static">
                                {{ formatDate(receivable.issue_date) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Valor Total</label>
                            <p class="form-control-static">
                                {{ formatCurrency(receivable.total_amount) }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Valor Pago</label>
                            <p class="form-control-static">
                                {{ formatCurrency(receivable.paid_amount) }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Saldo</label>
                            <p class="form-control-static">
                                {{
                                    formatCurrency(receivable.remaining_amount)
                                }}
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
                                        receivable.status === 'paid'
                                            ? 'badge-success'
                                            : receivable.status === 'partial'
                                            ? 'badge-warning'
                                            : 'badge-secondary',
                                    ]"
                                >
                                    {{
                                        receivable.status === "paid"
                                            ? "Pago"
                                            : receivable.status === "partial"
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
