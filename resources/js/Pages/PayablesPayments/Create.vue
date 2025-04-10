<script setup>
import { ref, computed, onMounted } from "vue";
import { useForm, Link, Head } from "@inertiajs/vue3";
import InputField from "@/Components/InputField.vue";
import Select2 from "@/Components/Select2.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { formatCurrency, formatSequentialId } from "@/utils";

const props = defineProps({
    payables: {
        type: Array,
        required: true,
    },
});

const validationError = ref("");
const payments = ref([]);

onMounted(() => {
    if (props.payables && props.payables.length > 0) {
        payments.value = props.payables.map((payable) => ({
            payable_id: payable.id,
            paid_amount: payable.remaining_amount,
            fees: 0,
            discount: 0,
            effective_amount: payable.remaining_amount,
        }));
    }
});

const parseLocaleNumber = (value) => {
    if (typeof value === "string") {
        return parseFloat(value.replace(",", ".")) || 0;
    }
    return parseFloat(value) || 0;
};

const totalPaidAmount = computed(() =>
    payments.value.reduce(
        (total, payment) => total + parseLocaleNumber(payment.paid_amount || 0),
        0
    )
);

const totalFees = computed(() =>
    payments.value.reduce(
        (total, payment) => total + parseLocaleNumber(payment.fees || 0),
        0
    )
);

const totalDiscounts = computed(() =>
    payments.value.reduce(
        (total, payment) => total + parseLocaleNumber(payment.discount || 0),
        0
    )
);

const effectiveTotal = computed(() =>
    payments.value.reduce(
        (total, payment) =>
            total +
            parseLocaleNumber(payment.paid_amount || 0) +
            parseLocaleNumber(payment.fees || 0) -
            parseLocaleNumber(payment.discount || 0),
        0
    )
);

const form = useForm({
    payable_ids: computed(() => props.payables.map((r) => r.id)),
    payment_method_id: "",
    account_id: "",
    payment_date: new Date().toISOString().slice(0, 10),
    payments: computed(() => payments.value),
    notes: "",
    total_paid_amount: computed(() => totalPaidAmount.value),
});

const validatePaymentAmounts = () => {
    let isValid = true;
    validationError.value = "";

    for (let i = 0; i < props.payables.length; i++) {
        const payable = props.payables[i];
        const payment = payments.value[i];

        if (
            parseLocaleNumber(payment.paid_amount) >
            parseLocaleNumber(payable.remaining_amount)
        ) {
            validationError.value = `O valor de pagamento não pode exceder o valor restante para o pagável ${formatSequentialId(
                payable.sequential_id
            )}.`;
            isValid = false;
            break;
        }
    }

    return isValid;
};

const handleSubmit = () => {
    if (!validatePaymentAmounts()) {
        return;
    }

    validationError.value = "";
    form.post(route("payables.payments.store"));
};

const calculateEffectiveAmount = (payment, index) => {
    const paid = parseLocaleNumber(payment.paid_amount || 0);
    const fees = parseLocaleNumber(payment.fees || 0);
    const discount = parseLocaleNumber(payment.discount || 0);

    payment.effective_amount = paid + fees - discount;
};
</script>

<template>
    <Head title="Registrar Pagamento" />
    <AuthenticatedLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Registrar Pagamento</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Pagáveis', routeName: 'payables.index' },
                        { label: 'Registrar Pagamento' },
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
            <div class="card-header">Detalhes do Pagamento</div>
            <div class="card-body">
                <div v-if="validationError" class="alert alert-danger">
                    {{ validationError }}
                </div>

                <h5>
                    Fornecedor:
                    <strong>
                        {{ props.payables[0].supplier.first_name }}
                        {{ " " }}
                        {{ props.payables[0].supplier.last_name }}
                    </strong>
                </h5>

                <form @submit.prevent="handleSubmit">
                    <div class="row class mt-4">
                        <div class="col-md-4">
                            <Select2
                                label="Método de Pagamento"
                                v-model="form.payment_method_id"
                                :error="form.errors.payment_method_id"
                                :search-url="
                                    route('api.payment-methods.search')
                                "
                                value-key="id"
                                label-key="name"
                                placeholder="Pesquisar"
                                required
                            />
                        </div>

                        <div class="col-md-4">
                            <Select2
                                label="Conta"
                                v-model="form.account_id"
                                :error="form.errors.account_id"
                                :search-url="route('api.accounts.search')"
                                value-key="id"
                                label-key="name"
                                placeholder="Pesquisar"
                                required
                            />
                        </div>

                        <div class="col-md-4">
                            <InputField
                                id="payment_date"
                                label="Data de Pagamento"
                                v-model="form.payment_date"
                                type="date"
                                :error="form.errors.payment_date"
                                required
                            />
                        </div>
                    </div>

                    <h5 class="mt-3">Detalhes dos Pagáveis</h5>

                    <div class="table-responsive">
                        <table
                            class="table table-bordered table-striped table-hover"
                        >
                            <thead>
                                <tr class="text-nowrap">
                                    <th>Código</th>
                                    <th>Vencimento</th>
                                    <th>Valor Total</th>
                                    <th>Valor Pago</th>
                                    <th>Valor Saldo</th>
                                    <th>Valor a Pagar</th>
                                    <th>Acréscimos</th>
                                    <th>Descontos</th>
                                    <th>Valor Efetivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(payable, index) in payables"
                                    :key="payable.id"
                                >
                                    <td>
                                        {{
                                            formatSequentialId(
                                                payable.sequential_id
                                            )
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            new Date(
                                                payable.due_date
                                            ).toLocaleDateString("pt-BR")
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            formatCurrency(payable.total_amount)
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            formatCurrency(payable.paid_amount)
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            formatCurrency(
                                                payable.remaining_amount
                                            )
                                        }}
                                    </td>
                                    <td v-if="payments[index]">
                                        <InputField
                                            :id="`paid_amount_${index}`"
                                            v-model="
                                                payments[index].paid_amount
                                            "
                                            maskType="currency"
                                            :error="
                                                form.errors[
                                                    `payments.${index}.paid_amount`
                                                ]
                                            "
                                            @update:modelValue="
                                                calculateEffectiveAmount(
                                                    payments[index],
                                                    index
                                                )
                                            "
                                            required
                                        />
                                        <div
                                            v-if="
                                                parseLocaleNumber(
                                                    payments[index].paid_amount
                                                ) >
                                                parseLocaleNumber(
                                                    payable.remaining_amount
                                                )
                                            "
                                            class="text-danger small"
                                        >
                                            Valor excede o saldo restante
                                        </div>
                                    </td>
                                    <td v-if="payments[index]">
                                        <InputField
                                            :id="`fees_${index}`"
                                            v-model="payments[index].fees"
                                            maskType="currency"
                                            :error="
                                                form.errors[
                                                    `payments.${index}.fees`
                                                ]
                                            "
                                            @update:modelValue="
                                                calculateEffectiveAmount(
                                                    payments[index],
                                                    index
                                                )
                                            "
                                        />
                                    </td>
                                    <td v-if="payments[index]">
                                        <InputField
                                            :id="`discount_${index}`"
                                            v-model="payments[index].discount"
                                            maskType="currency"
                                            :error="
                                                form.errors[
                                                    `payments.${index}.discount`
                                                ]
                                            "
                                            @update:modelValue="
                                                calculateEffectiveAmount(
                                                    payments[index],
                                                    index
                                                )
                                            "
                                        />
                                    </td>
                                    <td v-if="payments[index]">
                                        {{
                                            formatCurrency(
                                                payments[index].effective_amount
                                            )
                                        }}
                                    </td>
                                    <td v-else colspan="4">Carregando...</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-right">
                                        Total
                                    </th>
                                    <th>
                                        {{ formatCurrency(totalPaidAmount) }}
                                    </th>
                                    <th>{{ formatCurrency(totalFees) }}</th>
                                    <th>
                                        {{ formatCurrency(totalDiscounts) }}
                                    </th>
                                    <th>
                                        {{ formatCurrency(effectiveTotal) }}
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="form-group mt-3">
                        <label for="notes">Observações</label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            class="form-control"
                            rows="2"
                            :class="{ 'is-invalid': form.errors.notes }"
                        ></textarea>
                        <div class="invalid-feedback">
                            {{ form.errors.notes }}
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
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
                                <i class="fas fa-save"></i>
                                &nbsp; Registrar Pagamento
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
