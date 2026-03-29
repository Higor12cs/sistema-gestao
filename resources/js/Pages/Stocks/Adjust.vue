<!-- resources/js/Pages/Stocks/Adjust.vue -->
<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputField from "@/Components/InputField.vue";

const props = defineProps({
    stock: Object,
});

const form = useForm({
    new_quantity: props.stock.quantity,
    notes: "",
});

const submit = () => {
    form.post(route("stocks.store-adjustment", props.stock.id));
};
</script>

<template>
    <Head title="Ajustar Estoque" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Ajustar Estoque</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Estoque', routeName: 'stocks.index' },
                        { label: 'Ajuste' },
                    ]"
                />
            </div>
            <Link
                :href="route('stocks.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>

        <div class="card">
            <div class="card-header">Ajuste de Estoque</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h5>
                            Produto:
                            <strong>
                                {{ stock.product.name }}
                            </strong>
                        </h5>
                        <p class="text-muted">
                            Código:
                            {{ String(stock.product.id).padStart(6, "0") }}
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="stock">Estoque Atual</label>
                            <input
                                class="form-control"
                                id="stock"
                                :value="stock.quantity"
                                disabled
                            />
                        </div>

                        <div class="col-md-6">
                            <InputField
                                id="new_quantity"
                                v-model="form.new_quantity"
                                label="Nova Quantidade"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                :error="form.errors.new_quantity"
                            />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="notes">Descrição do Ajuste</label>
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.notes }"
                                    required
                                    rows="3"
                                ></textarea>
                                <div class="invalid-feedback">
                                    {{ form.errors.notes }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <strong>Atenção!</strong> Este ajuste irá alterar
                        diretamente a quantidade em estoque. Por favor,
                        certifique-se de inserir o valor correto e uma descrição
                        adequada para o ajuste.
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
                                &nbsp; Confirmar Ajuste
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
