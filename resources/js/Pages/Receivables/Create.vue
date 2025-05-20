<script setup>
import { ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputField from "@/Components/InputField.vue";
import Select2 from "@/Components/Select2.vue";

const loading = ref(false);
const receivablesCount = ref(1);

const form = useForm({
    receivables: [
        {
            customer_id: "",
            chart_account_id: "",
            payment_method_id: "",
            issue_date: new Date().toISOString().slice(0, 10),
            due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000)
                .toISOString()
                .slice(0, 10),
            total_amount: "",
            description: "",
        },
    ],
});

const addReceivable = () => {
    const lastDueDate = new Date(
        form.receivables[form.receivables.length - 1].due_date
    );
    const newDueDate = new Date(lastDueDate.setDate(lastDueDate.getDate() + 30))
        .toISOString()
        .slice(0, 10);

    form.receivables.push({
        customer_id: form.receivables[0].customer_id,
        chart_account_id: form.receivables[0].chart_account_id,
        payment_method_id: form.receivables[0].payment_method_id,
        issue_date: form.receivables[0].issue_date,
        due_date: newDueDate,
        total_amount: "",
        description: "",
    });
    receivablesCount.value++;
};

const removeReceivable = (index) => {
    form.receivables.splice(index, 1);
    receivablesCount.value--;
};

const handleSubmit = () => {
    loading.value = true;
    form.post(route("receivables.store"), {
        onSuccess: () => {
            loading.value = false;
        },
        onError: () => {
            loading.value = false;
        },
    });
};

const updateCustomerForAll = (newCustomerId) => {
    form.receivables.forEach((receivable) => {
        receivable.customer_id = newCustomerId;
    });
};

const updateChartAccountForAll = (newMethodId) => {
    form.receivables.forEach((payable) => {
        payable.chart_account_id = newMethodId;
    });
};

const updatePaymentMethodForAll = (newMethodId) => {
    form.receivables.forEach((receivable) => {
        receivable.payment_method_id = newMethodId;
    });
};

const updateIssueDateForAll = (newDate) => {
    form.receivables.forEach((receivable) => {
        receivable.issue_date = newDate;
    });
};
</script>

<template>
    <Head title="Criar Recebíveis" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Criar Recebíveis</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Recebíveis', routeName: 'receivables.index' },
                        { label: 'Criar' },
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
            <div class="card-header">Cadastro de Recebíveis</div>
            <div class="card-body">
                <form @submit.prevent="handleSubmit">
                    <div class="row">
                        <div class="col-md-6">
                            <Select2
                                label="Cliente"
                                v-model="form.receivables[0].customer_id"
                                :error="
                                    form.errors['receivables.0.customer_id']
                                "
                                :search-url="route('api.customers.search')"
                                value-key="id"
                                label-key="name"
                                placeholder="Pesquisar"
                                required
                                @update:modelValue="updateCustomerForAll"
                            />
                        </div>
                        <div class="col-md-6">
                            <Select2
                                label="Plano de Conta"
                                v-model="form.receivables[0].chart_account_id"
                                :error="
                                    form.errors[
                                        'receivables.0.chart_account_id'
                                    ]
                                "
                                :search-url="route('api.chart-accounts.search')"
                                value-key="id"
                                label-key="name"
                                placeholder="Pesquisar"
                                required
                                @update:modelValue="updateChartAccountForAll"
                            />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <Select2
                                label="Método de Pagamento"
                                v-model="form.receivables[0].payment_method_id"
                                :error="
                                    form.errors[
                                        'receivables.0.payment_method_id'
                                    ]
                                "
                                :search-url="
                                    route('api.payment-methods.search')
                                "
                                value-key="id"
                                label-key="name"
                                placeholder="Pesquisar"
                                required
                                @update:modelValue="updatePaymentMethodForAll"
                            />
                        </div>

                        <div class="col-md-6">
                            <InputField
                                id="issue_date"
                                label="Data de Emissão"
                                v-model="form.receivables[0].issue_date"
                                type="date"
                                :error="form.errors['receivables.0.issue_date']"
                                required
                                @update:modelValue="updateIssueDateForAll"
                            />
                        </div>
                    </div>

                    <div class="table-responsive mt-4">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="col-1">#</th>
                                    <th class="col-3">Data de Vencimento</th>
                                    <th class="col-3">Valor Total</th>
                                    <th class="col-4">Descrição</th>
                                    <th class="col-1">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(
                                        receivable, index
                                    ) in form.receivables"
                                    :key="index"
                                >
                                    <td>
                                        {{
                                            (index + 1)
                                                .toString()
                                                .padStart(3, "0")
                                        }}
                                    </td>
                                    <td>
                                        <InputField
                                            :id="`due_date_${index}`"
                                            v-model="receivable.due_date"
                                            type="date"
                                            :error="
                                                form.errors[
                                                    `receivables.${index}.due_date`
                                                ]
                                            "
                                            required
                                        />
                                    </td>
                                    <td>
                                        <InputField
                                            :id="`total_amount_${index}`"
                                            v-model="receivable.total_amount"
                                            maskType="currency"
                                            :error="
                                                form.errors[
                                                    `receivables.${index}.total_amount`
                                                ]
                                            "
                                            required
                                        />
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            class="form-control"
                                            v-model="receivable.description"
                                            :class="{
                                                'is-invalid':
                                                    form.errors[
                                                        `receivables.${index}.description`
                                                    ],
                                            }"
                                        />
                                        <div class="invalid-feedback">
                                            {{
                                                form.errors[
                                                    `receivables.${index}.description`
                                                ]
                                            }}
                                        </div>
                                    </td>
                                    <td>
                                        <button
                                            :disabled="
                                                form.receivables.length <= 1
                                            "
                                            type="button"
                                            class="btn btn-sm btn-danger"
                                            @click="removeReceivable(index)"
                                        >
                                            Excluir
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-info"
                                            @click="addReceivable"
                                        >
                                            <i class="fas fa-plus"></i>
                                            &nbsp; Adicionar Recebível
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
    </AppLayout>
</template>
