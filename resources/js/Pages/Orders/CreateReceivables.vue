<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Select2 from "@/Components/Select2.vue";
import InputField from "@/Components/InputField.vue";
import { ref, computed, watch } from "vue";
import { formatCurrency } from "@/utils";

const props = defineProps({
    order: Object,
});

const convertToNumber = (value) => {
    if (value === null || value === undefined || value === "") {
        return 0;
    }

    if (typeof value === "number") {
        return value;
    }

    let valueStr = String(value).trim();

    valueStr = valueStr.replace(/R\$\s*/g, "");

    if (valueStr.includes(",")) {
        valueStr = valueStr.replace(/\./g, "").replace(",", ".");
    }

    const number = parseFloat(valueStr);
    return number;
};

const getCurrentDate = () => {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, "0");
    const day = String(now.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
};

const getDayFromDateString = (dateString) => {
    if (!dateString) return 1;
    return parseInt(dateString.slice(8, 10));
};

const addMonthsToDateString = (dateString, monthsToAdd) => {
    if (!dateString) return "";

    const year = parseInt(dateString.slice(0, 4));
    const month = parseInt(dateString.slice(5, 7)) - 1;
    const day = parseInt(dateString.slice(8, 10));

    const newMonth = month + monthsToAdd;
    const newYear = year + Math.floor(newMonth / 12);
    const adjustedMonth = newMonth % 12;

    const lastDayOfTargetMonth = new Date(
        newYear,
        adjustedMonth + 1,
        0
    ).getDate();

    const adjustedDay = Math.min(day, lastDayOfTargetMonth);

    return `${newYear}-${String(adjustedMonth + 1).padStart(2, "0")}-${String(
        adjustedDay
    ).padStart(2, "0")}`;
};

const setDayInDateString = (dateString, newDay) => {
    if (!dateString) return "";

    const year = parseInt(dateString.slice(0, 4));
    const month = parseInt(dateString.slice(5, 7)) - 1;

    const lastDayOfMonth = new Date(year, month + 1, 0).getDate();

    const adjustedDay = Math.min(newDay, lastDayOfMonth);

    return `${year}-${String(month + 1).padStart(2, "0")}-${String(
        adjustedDay
    ).padStart(2, "0")}`;
};

const remainingAmount = ref(props.order.total_price);

const form = useForm({
    receivables: [],
});

const currentDate = getCurrentDate();

const installmentForm = useForm({
    amount: props.order.total_price,
    payment_method_id: "",
    first_due_date: currentDate,
    installments: 1,
    due_day: getDayFromDateString(currentDate),
});

watch(
    () => installmentForm.first_due_date,
    (newValue) => {
        if (newValue) {
            installmentForm.due_day = getDayFromDateString(newValue);
        }
    },
    { immediate: true }
);

const generateInstallments = () => {
    const amount = convertToNumber(installmentForm.amount);
    const paymentMethodId = installmentForm.payment_method_id;
    const firstDueDate = installmentForm.first_due_date;
    const installmentsCount = installmentForm.installments;
    const dueDay = installmentForm.due_day;

    if (!paymentMethodId || amount <= 0 || installmentsCount <= 0) {
        return;
    }

    const baseInstallmentAmount =
        Math.floor((amount / installmentsCount) * 100) / 100;
    const remainder =
        Math.round((amount - baseInstallmentAmount * installmentsCount) * 100) /
        100;

    const newReceivables = [];

    for (let i = 0; i < installmentsCount; i++) {
        let dueDate;

        if (i === 0) {
            dueDate = firstDueDate;
        } else {
            const nextMonthDate = addMonthsToDateString(firstDueDate, i);
            dueDate = setDayInDateString(nextMonthDate, dueDay);
        }

        const installmentAmount =
            i === installmentsCount - 1
                ? parseFloat((baseInstallmentAmount + remainder).toFixed(2))
                : baseInstallmentAmount;

        newReceivables.push({
            payment_method_id: paymentMethodId,
            due_date: dueDate,
            amount: installmentAmount,
            description: `RECEBÍVEL PEDIDO #${String(
                props.order.sequential_id
            ).padStart(6, "0")} - ${i + 1}/${installmentsCount}`,
        });
    }

    form.receivables = [...form.receivables, ...newReceivables];
    installmentForm.reset();
    installmentForm.amount =
        remainingAmount.value > 0 ? remainingAmount.value : 0;
    installmentForm.first_due_date = currentDate;
    installmentForm.installments = 1;
    installmentForm.due_day = getDayFromDateString(currentDate);
};

const addReceivable = () => {
    const newDueDate =
        form.receivables.length > 0
            ? addMonthsToDateString(
                  form.receivables[form.receivables.length - 1].due_date,
                  1
              )
            : currentDate;

    form.receivables.push({
        payment_method_id: "",
        due_date: newDueDate,
        amount: remainingAmount.value > 0 ? remainingAmount.value : 0,
        description: `RECEBÍVEL PEDIDO #${String(
            props.order.sequential_id
        ).padStart(6, "0")}`,
    });
};

const removeReceivable = (index) => {
    form.receivables.splice(index, 1);
};

const totalReceivables = computed(() => {
    return form.receivables.reduce((sum, item) => {
        const amount = parseFloat(convertToNumber(item.amount) || 0);
        return sum + amount;
    }, 0);
});

const difference = computed(() => {
    const total = Math.round(totalReceivables.value * 100) / 100;
    const orderTotal = Math.round(props.order.total_price * 100) / 100;
    return Math.round((total - orderTotal) * 100) / 100;
});

const isValid = computed(() => {
    return difference.value === 0;
});

watch(
    totalReceivables,
    (newTotal) => {
        remainingAmount.value = Math.max(0, props.order.total_price - newTotal);
        installmentForm.amount = remainingAmount.value;
    },
    { immediate: true }
);

const submit = () => {
    if (!isValid.value) {
        return;
    }

    form.post(route("orders.store-receivables", props.order.id));
};

const updateDueDates = (index) => {
    const selectedDate = form.receivables[index].due_date;
    const dueDay = getDayFromDateString(selectedDate);

    for (let i = index + 1; i < form.receivables.length; i++) {
        const currentDate = form.receivables[i].due_date;
        form.receivables[i].due_date = setDayInDateString(currentDate, dueDay);
    }
};

const adjustLastReceivable = () => {
    if (form.receivables.length > 0) {
        const lastIndex = form.receivables.length - 1;
        const currentLastAmount = convertToNumber(
            form.receivables[lastIndex].amount
        );
        const adjustedAmount =
            Math.round((currentLastAmount - difference.value) * 100) / 100;

        form.receivables[lastIndex].amount = adjustedAmount;
    }
};
</script>

<template>
    <Head title="Finalizar Pedido" />
    <AuthenticatedLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>
                    Finalizar Pedido #{{
                        String(order.sequential_id).padStart(6, "0")
                    }}
                </h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Pedidos', routeName: 'orders.index' },
                        { label: 'Finalizar' },
                    ]"
                />
            </div>

            <Link
                :href="route('orders.show', order.sequential_id)"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>

        <div class="card">
            <div class="card-header">Informações do Pedido</div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <h5>
                            Cliente:
                            {{ " " }}
                            <strong>{{ order.customer.first_name }}</strong>
                        </h5>
                    </div>
                    <div class="col-md-12">
                        <h5>
                            Valor Total do Pedido:
                            {{ " " }}
                            <strong class="text-success">{{
                                formatCurrency(order.total_price)
                            }}</strong>
                        </h5>
                    </div>
                    <div v-if="remainingAmount.value > 0" class="col-md-12">
                        <h5>
                            Valor Restante:
                            {{ " " }}
                            <strong class="text-warning">{{
                                formatCurrency(remainingAmount)
                            }}</strong>
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">Gerar Recebíveis</div>
            <div class="card-body">
                <form @submit.prevent="generateInstallments">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label for="amount">Valor a Parcelar</label>
                                <InputField
                                    id="amount"
                                    v-model="installmentForm.amount"
                                    maskType="currency"
                                    required
                                    class="mt-auto"
                                />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label for="payment_method"
                                    >Método de Pagamento</label
                                >
                                <Select2
                                    id="payment_method"
                                    v-model="installmentForm.payment_method_id"
                                    :search-url="
                                        route('api.payment-methods.search')
                                    "
                                    placeholder="Selecione"
                                    required
                                    class="mt-auto"
                                />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-0">
                                <label for="first_due_date"
                                    >Primeira Data de Vencimento</label
                                >
                                <InputField
                                    id="first_due_date"
                                    v-model="installmentForm.first_due_date"
                                    type="date"
                                    required
                                    class="mt-auto"
                                />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-0">
                                <label for="installments"
                                    >Quantidade de Parcelas</label
                                >
                                <InputField
                                    id="installments"
                                    v-model="installmentForm.installments"
                                    type="number"
                                    min="1"
                                    required
                                    class="mt-auto"
                                />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-0">
                                <label for="due_day">Dia de Vencimento</label>
                                <InputField
                                    id="due_day"
                                    v-model="installmentForm.due_day"
                                    type="number"
                                    min="1"
                                    max="31"
                                    required
                                    class="mt-auto"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button
                            type="submit"
                            class="btn btn-primary"
                            :disabled="
                                !installmentForm.payment_method_id ||
                                installmentForm.amount <= 0 ||
                                installmentForm.installments <= 0
                            "
                        >
                            <i class="fas fa-calculator"></i>
                            &nbsp; Gerar Parcelas
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">Recebíveis Gerados</div>
            <div class="card-body">
                <form id="receivables-form" @submit.prevent="submit">
                    <div
                        v-if="form.receivables.length === 0"
                        class="alert alert-info"
                    >
                        Nenhum recebível gerado. Use o formulário acima para
                        gerar parcelas ou clique em "Adicionar Recebível" para
                        adicionar manualmente.
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="col-1">#</th>
                                    <th class="col-3">Método de Pagamento</th>
                                    <th class="col-2">Data de Vencimento</th>
                                    <th class="col-2">Valor</th>
                                    <th class="col-3">Descrição</th>
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
                                        <Select2
                                            v-model="
                                                receivable.payment_method_id
                                            "
                                            :search-url="
                                                route(
                                                    'api.payment-methods.search'
                                                )
                                            "
                                            :class="{
                                                'is-invalid':
                                                    form.errors[
                                                        `receivables.${index}.payment_method_id`
                                                    ],
                                            }"
                                            placeholder="Selecione"
                                            required
                                        />
                                        <div class="invalid-feedback">
                                            {{
                                                form.errors[
                                                    `receivables.${index}.payment_method_id`
                                                ]
                                            }}
                                        </div>
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
                                            @update:modelValue="
                                                updateDueDates(index)
                                            "
                                        />
                                    </td>
                                    <td>
                                        <InputField
                                            :id="`amount_${index}`"
                                            v-model="receivable.amount"
                                            maskType="currency"
                                            :error="
                                                form.errors[
                                                    `receivables.${index}.amount`
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
                                            type="button"
                                            class="btn btn-sm btn-danger"
                                            @click="removeReceivable(index)"
                                        >
                                            Excluir
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex">
                        <button
                            type="button"
                            class="btn btn-sm btn-info"
                            @click="addReceivable"
                        >
                            <i class="fas fa-plus"></i>
                            &nbsp; Adicionar Recebível
                        </button>

                        <button
                            v-if="!isValid && form.receivables.length > 0"
                            type="button"
                            class="btn btn-sm btn-warning ml-2"
                            @click="adjustLastReceivable"
                        >
                            <i class="fas fa-balance-scale"></i>
                            &nbsp; Ajustar Última Parcela
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">Resumo</div>
            <div class="card-body px-0 pb-0">
                <table class="table">
                    <tbody>
                        <tr>
                            <td class="px-3">
                                <strong>Total do Pedido:</strong>
                            </td>
                            <td class="px-3 text-right">
                                {{ formatCurrency(order.total_price) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="px-3">
                                <strong>Total dos Recebíveis:</strong>
                            </td>
                            <td class="px-3 text-right">
                                {{ formatCurrency(totalReceivables) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="px-3"><strong>Diferença:</strong></td>
                            <td
                                class="px-3 text-right"
                                :class="{
                                    'text-success': isValid,
                                    'text-danger': !isValid,
                                }"
                            >
                                {{ formatCurrency(difference) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="!isValid" class="alert alert-danger mt-3">
            <strong>Atenção:</strong> O valor total dos recebíveis deve ser
            <strong>exatamente</strong> igual ao valor do pedido.

            <div class="mt-2">
                <div>
                    <strong>Valor do Pedido:</strong>
                    {{ formatCurrency(order.total_price) }}
                </div>
                <div>
                    <strong>Valor Total dos Recebíveis:</strong>
                    {{ formatCurrency(totalReceivables) }}
                </div>
                <div>
                    <strong>Diferença:</strong> {{ formatCurrency(difference) }}
                </div>
            </div>

            <div class="mt-2">
                Para corrigir automaticamente esta diferença, clique no botão
                <strong>"Ajustar Última Parcela"</strong> acima.
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <button
                type="button"
                class="btn btn-primary"
                :disabled="
                    !isValid || form.processing || form.receivables.length === 0
                "
                @click="submit"
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
                    &nbsp; Finalizar Pedido
                </span>
            </button>
        </div>
    </AuthenticatedLayout>
</template>
