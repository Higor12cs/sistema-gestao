<script setup>
import { ref, computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputField from "@/Components/InputField.vue";
import { formatCurrency } from "@/utils";

const props = defineProps({
    accounts: Array,
});

const form = useForm({
    source_account_id: "",
    destination_account_id: "",
    amount: "",
    transfer_date: new Date().toISOString().split("T")[0],
    notes: "",
});

const sourceAccount = computed(() => {
    if (!form.source_account_id) return null;
    return props.accounts.find(
        (account) => account.id === form.source_account_id
    );
});

const destinationAccount = computed(() => {
    if (!form.destination_account_id) return null;
    return props.accounts.find(
        (account) => account.id === form.destination_account_id
    );
});

const handleSubmit = () => {
    form.post(route("account-transfers.store"));
};
</script>

<template>
    <Head title="Nova Transferência" />
    <AuthenticatedLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Nova Transferência</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        {
                            label: 'Transferências',
                            routeName: 'account-transfers.index',
                        },
                        { label: 'Nova Transferência' },
                    ]"
                />
            </div>
            <Link
                :href="route('account-transfers.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>

        <div class="card">
            <div class="card-header">Nova Transferência</div>
            <div class="card-body">
                <form @submit.prevent="handleSubmit">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="source_account_id">
                                    Conta de Origem
                                    <span class="text-danger">*</span>
                                </label>
                                <select
                                    id="source_account_id"
                                    v-model="form.source_account_id"
                                    class="form-control"
                                    :class="{
                                        'is-invalid':
                                            form.errors.source_account_id,
                                    }"
                                    required
                                >
                                    <option value="" disabled>
                                        Selecione a Conta de Origem
                                    </option>
                                    <option
                                        v-for="account in accounts"
                                        :key="account.id"
                                        :value="account.id"
                                    >
                                        {{ account.name }} -
                                        {{
                                            formatCurrency(
                                                account.current_balance
                                            )
                                        }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.source_account_id"
                                    class="invalid-feedback"
                                >
                                    {{ form.errors.source_account_id }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="destination_account_id">
                                    Conta de Destino
                                    <span class="text-danger">*</span>
                                </label>
                                <select
                                    id="destination_account_id"
                                    v-model="form.destination_account_id"
                                    class="form-control"
                                    :class="{
                                        'is-invalid':
                                            form.errors.destination_account_id,
                                    }"
                                    required
                                >
                                    <option value="" disabled>
                                        Selecione a Conta de Destino
                                    </option>
                                    <option
                                        v-for="account in accounts"
                                        :key="account.id"
                                        :value="account.id"
                                        :disabled="
                                            account.id ===
                                            form.source_account_id
                                        "
                                    >
                                        {{ account.name }} -
                                        {{
                                            formatCurrency(
                                                account.current_balance
                                            )
                                        }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.destination_account_id"
                                    class="invalid-feedback"
                                >
                                    {{ form.errors.destination_account_id }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <InputField
                                id="amount"
                                label="Valor da Transferência"
                                v-model="form.amount"
                                maskType="currency"
                                :error="form.errors.amount"
                                required
                            />
                        </div>
                        <div class="col-md-6">
                            <InputField
                                id="transfer_date"
                                label="Data da Transferência"
                                v-model="form.transfer_date"
                                type="date"
                                :error="form.errors.transfer_date"
                                required
                            />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="notes">Observações</label>
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.notes }"
                                    rows="2"
                                ></textarea>
                                <div
                                    v-if="form.errors.notes"
                                    class="invalid-feedback"
                                >
                                    {{ form.errors.notes }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6" v-if="sourceAccount">
                            <div class="alert alert-info">
                                <strong>Saldo na Conta de Origem:</strong>
                                {{
                                    formatCurrency(
                                        sourceAccount.current_balance
                                    )
                                }}
                            </div>
                        </div>
                        <div class="col-md-6" v-if="destinationAccount">
                            <div class="alert alert-info">
                                <strong>Saldo na Conta de Destino:</strong>
                                {{
                                    formatCurrency(
                                        destinationAccount.current_balance
                                    )
                                }}
                            </div>
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
                            <span v-if="form.processing">Processando...</span>
                            <span v-else>
                                <i class="fas fa-exchange-alt"></i>
                                &nbsp; Realizar Transferência
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
