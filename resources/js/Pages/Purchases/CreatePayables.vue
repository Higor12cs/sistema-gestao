<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Select2 from "@/Components/Select2.vue";
import InputField from "@/Components/InputField.vue";
import { ref, computed, watch } from "vue";

const props = defineProps({
    purchase: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat("pt-BR", {
        style: "currency",
        currency: "BRL",
    }).format(value);
};

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

const remainingAmount = ref(props.purchase.total_cost);

const form = useForm({
    payables: [],
});

const currentDate = getCurrentDate();

const installmentForm = useForm({
    amount: props.purchase.total_cost,
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

    const newPayables = [];

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

        newPayables.push({
            payment_method_id: paymentMethodId,
            due_date: dueDate,
            amount: installmentAmount,
            description: `PAGÁVEL COMPRA #${String(
                props.purchase.sequential_id
            ).padStart(6, "0")} - ${i + 1}/${installmentsCount}`,
        });
    }

    form.payables = [...form.payables, ...newPayables];
    installmentForm.reset();
    installmentForm.amount =
        remainingAmount.value > 0 ? remainingAmount.value : 0;
    installmentForm.first_due_date = currentDate;
    installmentForm.installments = 1;
    installmentForm.due_day = getDayFromDateString(currentDate);
};

const addPayable = () => {
    const newDueDate =
        form.payables.length > 0
            ? addMonthsToDateString(
                  form.payables[form.payables.length - 1].due_date,
                  1
              )
            : currentDate;

    form.payables.push({
        payment_method_id: "",
        due_date: newDueDate,
        amount: remainingAmount.value > 0 ? remainingAmount.value : 0,
        description: `PAGÁVEL COMPRA #${String(
            props.purchase.sequential_id
        ).padStart(6, "0")}`,
    });
};

const removePayable = (index) => {
    form.payables.splice(index, 1);
};

const totalPayables = computed(() => {
    return form.payables.reduce((sum, item) => {
        const amount = parseFloat(convertToNumber(item.amount) || 0);
        return sum + amount;
    }, 0);
});

const difference = computed(() => {
    const total = Math.round(totalPayables.value * 100) / 100;
    const purchaseTotal = Math.round(props.purchase.total_cost * 100) / 100;
    return Math.round((total - purchaseTotal) * 100) / 100;
});

const isValid = computed(() => {
    return difference.value === 0;
});

watch(
    totalPayables,
    (newTotal) => {
        remainingAmount.value = Math.max(
            0,
            props.purchase.total_cost - newTotal
        );
        installmentForm.amount = remainingAmount.value;
    },
    { immediate: true }
);

const submit = () => {
    if (!isValid.value) {
        alert(
            "O valor total dos pagáveis deve ser exatamente igual ao valor da compra."
        );
        return;
    }
    form.post(route("purchases.store-payables", props.purchase.id));
};

const updateDueDates = (index) => {
    const selectedDate = form.payables[index].due_date;
    const dueDay = getDayFromDateString(selectedDate);

    for (let i = index + 1; i < form.payables.length; i++) {
        const currentDate = form.payables[i].due_date;
        form.payables[i].due_date = setDayInDateString(currentDate, dueDay);
    }
};

const adjustLastPayable = () => {
    if (form.payables.length > 0) {
        const lastIndex = form.payables.length - 1;
        const currentLastAmount = convertToNumber(
            form.payables[lastIndex].amount
        );
        const adjustedAmount =
            Math.round((currentLastAmount - difference.value) * 100) / 100;

        form.payables[lastIndex].amount = adjustedAmount;
    }
};
</script>

<template>
    <Head title="Finalizar Compra" />
    <AuthenticatedLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>
                    Finalizar Compra #{{
                        String(purchase.sequential_id).padStart(6, "0")
                    }}
                </h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Compras', routeName: 'purchases.index' },
                        { label: 'Finalizar' },
                    ]"
                />
            </div>

            <Link
                :href="route('purchases.show', purchase.sequential_id)"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>

        <div class="card">
            <div class="card-header">Informações da Compra</div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <h5>
                            Fornecedor:
                            {{ " " }}
                            <strong>{{ purchase.supplier.first_name }}</strong>
                        </h5>
                    </div>
                    <div class="col-md-12">
                        <h5>
                            Valor Total da Compra:
                            {{ " " }}
                            <strong class="text-success">{{
                                formatCurrency(purchase.total_cost)
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
            <div class="card-header">Gerar Pagáveis</div>
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
                            class="btn btn-success"
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
            <div class="card-header">Pagáveis Gerados</div>
            <div class="card-body">
                <form id="payables-form" @submit.prevent="submit">
                    <div
                        v-if="form.payables.length === 0"
                        class="alert alert-info"
                    >
                        Nenhum pagável gerado. Use o formulário acima para gerar
                        parcelas ou clique em "Adicionar Pagável" para adicionar
                        manualmente.
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
                                    v-for="(payable, index) in form.payables"
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
                                            v-model="payable.payment_method_id"
                                            :search-url="
                                                route(
                                                    'api.payment-methods.search'
                                                )
                                            "
                                            :class="{
                                                'is-invalid':
                                                    form.errors[
                                                        `payables.${index}.payment_method_id`
                                                    ],
                                            }"
                                            placeholder="Selecione"
                                            required
                                        />
                                        <div class="invalid-feedback">
                                            {{
                                                form.errors[
                                                    `payables.${index}.payment_method_id`
                                                ]
                                            }}
                                        </div>
                                    </td>
                                    <td>
                                        <InputField
                                            :id="`due_date_${index}`"
                                            v-model="payable.due_date"
                                            type="date"
                                            :error="
                                                form.errors[
                                                    `payables.${index}.due_date`
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
                                            v-model="payable.amount"
                                            maskType="currency"
                                            :error="
                                                form.errors[
                                                    `payables.${index}.amount`
                                                ]
                                            "
                                            required
                                        />
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            class="form-control"
                                            v-model="payable.description"
                                            :class="{
                                                'is-invalid':
                                                    form.errors[
                                                        `payables.${index}.description`
                                                    ],
                                            }"
                                        />
                                        <div class="invalid-feedback">
                                            {{
                                                form.errors[
                                                    `payables.${index}.description`
                                                ]
                                            }}
                                        </div>
                                    </td>
                                    <td>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-danger"
                                            @click="removePayable(index)"
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
                            @click="addPayable"
                        >
                            <i class="fas fa-plus"></i>
                            &nbsp; Adicionar Pagável
                        </button>

                        <button
                            v-if="!isValid && form.payables.length > 0"
                            type="button"
                            class="btn btn-sm btn-warning ml-2"
                            @click="adjustLastPayable"
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
                                <strong>Total da Compra:</strong>
                            </td>
                            <td class="px-3 text-right">
                                {{ formatCurrency(purchase.total_cost) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="px-3">
                                <strong>Total dos Pagáveis:</strong>
                            </td>
                            <td class="px-3 text-right">
                                {{ formatCurrency(totalPayables) }}
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
            <strong>Atenção:</strong> O valor total dos pagáveis deve ser
            <strong>exatamente</strong> igual ao valor da compra.

            <div class="mt-2">
                <div>
                    <strong>Valor da Compra:</strong>
                    {{ formatCurrency(purchase.total_cost) }}
                </div>
                <div>
                    <strong>Valor Total dos Pagáveis:</strong>
                    {{ formatCurrency(totalPayables) }}
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
                    !isValid || form.processing || form.payables.length === 0
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
                    &nbsp; Finalizar Compra
                </span>
            </button>
        </div>
    </AuthenticatedLayout>
</template>
