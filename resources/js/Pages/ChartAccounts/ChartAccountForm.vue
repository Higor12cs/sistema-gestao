<script setup>
import { useForm } from "@inertiajs/vue3";
import InputField from "@/Components/InputField.vue";
import { computed } from "vue";

const props = defineProps({
    account: {
        type: Object,
        default: () => ({}),
    },
    potentialParents: {
        type: Array,
        default: () => [],
    },
    preselectedParentId: {
        type: String,
        default: null,
    },
    hasChildren: {
        type: Boolean,
        default: false,
    },
    processing: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["submit"]);

const form = useForm({
    _method: props.account.id ? "PUT" : "POST",
    name: props.account.name || "",
    description: props.account.description || "",
    parent_id: props.account.parent_id || props.preselectedParentId || null,
    allows_transactions:
        props.account.allows_transactions !== undefined
            ? props.account.allows_transactions
            : true,
    active: props.account.active !== undefined ? props.account.active : true,
    default_receivable:
        props.account.default_receivable !== undefined
            ? props.account.default_receivable
            : false,
    default_purchase:
        props.account.default_purchase !== undefined
            ? props.account.default_purchase
            : false,
});

const disableTransactions = computed(() => {
    return props.hasChildren;
});

const submit = () => {
    emit("submit", form);
};

defineExpose({ form });
</script>

<template>
    <form @submit.prevent="submit">
        <div class="row">
            <div class="col-12 mb-3">
                <label for="parent_id">Conta Pai (Opcional)</label>
                <select
                    id="parent_id"
                    v-model="form.parent_id"
                    class="form-control"
                >
                    <option :value="null">-- Nenhum (Conta Raiz) --</option>
                    <option
                        v-for="parent in potentialParents"
                        :key="parent.id"
                        :value="parent.id"
                    >
                        {{ parent.code }} - {{ parent.name }}
                    </option>
                </select>
                <div v-if="form.errors.parent_id" class="text-danger mt-1">
                    {{ form.errors.parent_id }}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <InputField
                    id="name"
                    label="Nome"
                    v-model="form.name"
                    :error="form.errors.name"
                    required
                    autofocus
                />
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    <label for="description">Descrição (Opcional)</label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        class="form-control"
                        rows="3"
                    ></textarea>
                    <div
                        v-if="form.errors.description"
                        class="text-danger mt-1"
                    >
                        {{ form.errors.description }}
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <div class="icheck-primary">
                    <input
                        type="checkbox"
                        id="allows_transactions"
                        v-model="form.allows_transactions"
                        :disabled="disableTransactions"
                    />
                    <label for="allows_transactions">
                        Permite Lançamentos
                        <i
                            v-if="disableTransactions"
                            class="fas fa-info-circle text-info ml-1"
                            title="Contas com subcontas não podem receber lançamentos"
                        ></i>
                    </label>
                </div>
                <div
                    v-if="form.errors.allows_transactions"
                    class="text-danger mt-1"
                >
                    {{ form.errors.allows_transactions }}
                </div>
            </div>
            <div class="col-6">
                <div class="icheck-primary">
                    <input type="checkbox" id="active" v-model="form.active" />
                    <label for="active">Ativo</label>
                </div>
                <div v-if="form.errors.active" class="text-danger mt-1">
                    {{ form.errors.active }}
                </div>
            </div>
        </div>

        <div class="row mb">
            <div class="col-6">
                <div class="icheck-primary">
                    <input
                        type="checkbox"
                        id="default_receivable"
                        v-model="form.default_receivable"
                        :disabled="disableTransactions"
                    />
                    <label for="default_receivable">
                        Padrão Recebíveis Pedidos
                        <i
                            v-if="disableTransactions"
                            class="fas fa-info-circle text-info ml-1"
                            title="Contas com subcontas não podem receber lançamentos"
                        ></i>
                    </label>
                </div>
                <div
                    v-if="form.errors.default_receivable"
                    class="text-danger mt-1"
                >
                    {{ form.errors.default_receivable }}
                </div>
            </div>

            <div class="col-6">
                <div class="icheck-primary">
                    <input
                        type="checkbox"
                        id="default_purchase"
                        v-model="form.default_purchase"
                        :disabled="disableTransactions"
                    />
                    <label for="default_purchase">
                        Padrão Pagáveis Compras
                        <i
                            v-if="disableTransactions"
                            class="fas fa-info-circle text-info ml-1"
                            title="Contas com subcontas não podem receber lançamentos"
                        ></i>
                    </label>
                </div>
                <div
                    v-if="form.errors.default_purchase"
                    class="text-danger mt-1"
                >
                    {{ form.errors.default_purchase }}
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <button
                type="submit"
                class="btn btn-primary"
                :disabled="processing"
            >
                <span
                    v-if="processing"
                    class="spinner-border spinner-border-sm mr-2"
                    role="status"
                    aria-hidden="true"
                ></span>
                <span v-if="processing">Salvando...</span>
                <span v-else>
                    <i class="fas fa-save"></i>
                    &nbsp; Salvar
                </span>
            </button>
        </div>
    </form>
</template>
