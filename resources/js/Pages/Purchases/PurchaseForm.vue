<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import Select2 from "@/Components/Select2.vue";
import InputField from "@/Components/InputField.vue";
import axios from "axios";
import { formatCurrency } from "@/Utils/Formatters.js";

const props = defineProps({
    purchase: Object,
});

const emit = defineEmits(["submit"]);

const parseLocaleNumber = (value) => {
    if (typeof value === "string") {
        return parseFloat(value.replace(",", ".")) || 0;
    }
    return parseFloat(value) || 0;
};

const discountValue = ref(parseLocaleNumber(props.purchase?.discount || 0));
const feesValue = ref(parseLocaleNumber(props.purchase?.fees || 0));
const networkError = ref(false);
const errorMessage = ref("");

const form = useForm({
    issue_date:
        props.purchase?.issue_date?.slice(0, 10) ||
        new Date().toISOString().slice(0, 10),
    supplier_id: props.purchase?.supplier_id || "",
    discount: props.purchase?.discount || 0,
    fees: props.purchase?.fees || 0,
    observation: props.purchase?.observation || "",
    items: props.purchase?.items?.map((item) => ({
        id: item.id,
        product_id: item.product_id,
        quantity: Number(item.quantity),
        unit_cost: Number(item.unit_cost),
        discount: Number(item.discount || 0),
        fees: Number(item.fees || 0),
    })) || [defaultItem()],
});

function defaultItem() {
    return {
        product_id: "",
        quantity: 1,
        unit_cost: 0,
        discount: 0,
        fees: 0,
    };
}

const suppliers = ref([]);
const products = ref([]);
const loading = ref(false);
const selectedProductInfo = ref({});

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => {
        const quantity = parseLocaleNumber(item.quantity);
        const unitCost = parseLocaleNumber(item.unit_cost);
        return sum + quantity * unitCost;
    }, 0);
});

const total = computed(() => {
    return subtotal.value - discountValue.value + feesValue.value;
});

const formattedTotal = computed(() => {
    return formatCurrency(total.value);
});

const getItemTotal = (item) => {
    const quantity = parseLocaleNumber(item.quantity);
    const unitCost = parseLocaleNumber(item.unit_cost);
    const discount = parseLocaleNumber(item.discount);
    const fees = parseLocaleNumber(item.fees);

    const totalValue = quantity * unitCost - discount + fees;
    return formatCurrency(totalValue);
};

const addItem = () => {
    form.items.push(defaultItem());
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const fetchProductDetails = async (productId, index) => {
    if (!productId) return;

    loading.value = true;
    networkError.value = false;

    try {
        const response = await axios.get(route("api.products.search"), {
            params: { ids: productId },
            timeout: 15000,
        });

        if (response.data.data.length > 0) {
            const product = response.data.data[0];
            selectedProductInfo.value[productId] = product;
            form.items[index].unit_cost = parseFloat(product.cost);
        }
    } catch (error) {
        console.error("Error fetching product details:", error);
        if (error.code === "ECONNABORTED") {
            errorMessage.value =
                "A requisição excedeu o tempo limite. Tente novamente.";
        } else {
            errorMessage.value =
                "Erro ao buscar detalhes do produto. Tente novamente.";
        }
        networkError.value = true;
    } finally {
        loading.value = false;
    }
};

const fetchInitialData = async () => {
    if (!props.purchase) return;

    networkError.value = false;

    try {
        if (props.purchase.supplier_id) {
            try {
                const response = await axios.get(
                    route("api.suppliers.search"),
                    {
                        params: { ids: props.purchase.supplier_id },
                        timeout: 15000,
                    }
                );
                suppliers.value = response.data.data;
            } catch (error) {
                console.error("Error fetching supplier:", error);
            }
        }

        const productIds = props.purchase.items
            ?.map((item) => item.product_id)
            ?.filter((id) => id)
            ?.join(",");

        if (productIds) {
            try {
                const response = await axios.get(route("api.products.search"), {
                    params: { ids: productIds },
                    timeout: 15000,
                });

                products.value = response.data.data;

                response.data.data.forEach((product) => {
                    selectedProductInfo.value[product.id] = product;
                });
            } catch (error) {
                if (error.code === "ECONNABORTED") {
                    errorMessage.value =
                        "A requisição excedeu o tempo limite. Tente novamente mais tarde.";
                } else {
                    errorMessage.value =
                        "Erro ao carregar produtos. Você pode continuar, mas os preços precisarão ser inseridos manualmente.";
                }
                networkError.value = true;
                console.error("Error fetching products:", error);
            }
        }
    } catch (error) {
        console.error("Error in fetchInitialData:", error);
        errorMessage.value = "Erro ao carregar dados iniciais.";
        networkError.value = true;
    }
};

const submit = () => {
    emit("submit", form);
};

const retryFetchData = () => {
    networkError.value = false;
    fetchInitialData();
};

watch(
    () => form.items,
    () => {
        form.items.forEach((item, index) => {
            form.items[index].quantity = parseLocaleNumber(item.quantity);
            form.items[index].unit_cost = parseLocaleNumber(item.unit_cost);
            form.items[index].discount = parseLocaleNumber(item.discount);
            form.items[index].fees = parseLocaleNumber(item.fees);
        });
    },
    { deep: true }
);

watch(
    () => form.discount,
    (newVal) => {
        discountValue.value = parseLocaleNumber(newVal);
    }
);

watch(
    () => form.fees,
    (newVal) => {
        feesValue.value = parseLocaleNumber(newVal);
    }
);

onMounted(() => {
    fetchInitialData();
    discountValue.value = parseLocaleNumber(form.discount);
    feesValue.value = parseLocaleNumber(form.fees);
});
</script>

<template>
    <form @submit.prevent="submit">
        <div v-if="networkError" class="alert alert-info mb-3">
            <strong>{{ errorMessage }}</strong>
            <button
                type="button"
                class="btn btn-sm btn-outline-secondary ml-2"
                @click="retryFetchData"
            >
                Tentar Novamente
            </button>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="supplier">Fornecedor</label>
                    <Select2
                        id="supplier"
                        v-model="form.supplier_id"
                        :options="suppliers"
                        :class="{ 'is-invalid': form.errors.supplier_id }"
                        placeholder="Pesquisar"
                        :searchUrl="route('api.suppliers.search')"
                        required
                        autofocus
                    />
                    <div class="invalid-feedback">
                        {{ form.errors.supplier_id }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="issue_date">Emissão</label>
                    <input
                        id="issue_date"
                        v-model="form.issue_date"
                        type="date"
                        class="form-control"
                        :class="{ 'is-invalid': form.errors.issue_date }"
                        required
                    />
                    <div class="invalid-feedback">
                        {{ form.errors.issue_date }}
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <div v-if="form.errors.items" class="alert alert-danger">
                {{ form.errors.items }}
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 40%">Produto</th>
                            <th>Quantidade</th>
                            <th>Preço Unitário</th>
                            <th>Preço Total</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in form.items" :key="index">
                            <td>
                                <Select2
                                    v-model="item.product_id"
                                    :options="products"
                                    :class="{
                                        'is-invalid':
                                            form.errors[
                                                `items.${index}.product_id`
                                            ],
                                    }"
                                    placeholder="Pesquisar"
                                    :searchUrl="route('api.products.search')"
                                    @change="
                                        fetchProductDetails(
                                            item.product_id,
                                            index
                                        )
                                    "
                                    required
                                />
                                <div class="invalid-feedback">
                                    {{
                                        form.errors[`items.${index}.product_id`]
                                    }}
                                </div>
                            </td>
                            <td>
                                <InputField
                                    :id="`quantity-${index}`"
                                    v-model.number="item.quantity"
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    :error="
                                        form.errors[`items.${index}.quantity`]
                                    "
                                    required
                                />
                            </td>
                            <td>
                                <InputField
                                    :id="`unit-cost-${index}`"
                                    v-model.number="item.unit_cost"
                                    maskType="currency"
                                    :error="
                                        form.errors[`items.${index}.unit_cost`]
                                    "
                                    required
                                />
                            </td>
                            <td>
                                <input
                                    type="text"
                                    class="form-control"
                                    readonly
                                    :value="getItemTotal(item)"
                                />
                            </td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-danger"
                                    @click="removeItem(index)"
                                    :disabled="form.items.length <= 1"
                                >
                                    Excluir
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-info"
                                    @click="addItem"
                                >
                                    <i class="fas fa-plus"></i>
                                    &nbsp; Adicionar Item
                                </button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="discount">Desconto Total</label>
                    <InputField
                        id="discount"
                        v-model="form.discount"
                        maskType="currency"
                        :error="form.errors.discount"
                    />
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="fees">Taxas Adicionais</label>
                    <InputField
                        id="fees"
                        v-model="form.fees"
                        maskType="currency"
                        :error="form.errors.fees"
                    />
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Total da Compra</label>
                    <input
                        type="text"
                        class="form-control"
                        :value="formattedTotal"
                        readonly
                    />
                </div>
            </div>
        </div>

        <div class="form-group mt-3">
            <label for="observation">Observações</label>
            <textarea
                id="observation"
                v-model="form.observation"
                class="form-control"
                :class="{ 'is-invalid': form.errors.observation }"
                rows="3"
            ></textarea>
            <div class="invalid-feedback">
                {{ form.errors.observation }}
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
                <span v-if="form.processing">Salvando...</span>
                <span v-else>
                    <i class="fas fa-save"></i>
                    &nbsp;
                    {{ props.purchase ? "Atualizar Compra" : "Criar Compra" }}
                </span>
            </button>
        </div>
    </form>
</template>
