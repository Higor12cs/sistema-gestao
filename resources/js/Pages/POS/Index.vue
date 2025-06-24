<script setup>
import { ref, computed, onMounted, nextTick } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Select2 from "@/Components/Select2.vue";
import InputField from "@/Components/InputField.vue";
import axios from "axios";
import { formatCurrency } from "@/Utils/Formatters.js";
import { toast } from "vue3-toastify";

const props = defineProps({
    defaultCustomer: Object,
    featuredProducts: Array,
    success: Object,
});

const form = useForm({
    customer_id: props.defaultCustomer?.id || "",
    items: [],
    discount: 0,
    fees: 0,
    payment_method: "cash",
    installments: 1,
    observation: "",
});

const barcodeInput = ref("");
const productSearch = ref("");
const selectedProducts = ref([]);
const loading = ref(false);
const showPaymentModal = ref(false);
const customers = ref(props.defaultCustomer ? [props.defaultCustomer] : []);

const parseLocaleNumber = (value) => {
    if (typeof value === "string") {
        return parseFloat(value.replace(",", ".")) || 0;
    }
    return parseFloat(value) || 0;
};

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => {
        const quantity = parseLocaleNumber(item.quantity);
        const unitPrice = parseLocaleNumber(item.unit_price);
        const discount = parseLocaleNumber(item.discount || 0);
        const fees = parseLocaleNumber(item.fees || 0);
        return sum + (quantity * unitPrice - discount + fees);
    }, 0);
});

const total = computed(() => {
    return (
        subtotal.value -
        parseLocaleNumber(form.discount) +
        parseLocaleNumber(form.fees)
    );
});

const formattedTotal = computed(() => {
    return formatCurrency(total.value);
});

const itemsCount = computed(() => {
    return form.items.reduce(
        (sum, item) => sum + parseLocaleNumber(item.quantity),
        0
    );
});

const addProductToCart = (product) => {
    const existingIndex = form.items.findIndex(
        (item) => item.product_id === product.id
    );

    if (existingIndex !== -1) {
        form.items[existingIndex].quantity += 1;
    } else {
        form.items.push({
            product_id: product.id,
            product_name: product.name,
            quantity: 1,
            unit_price: product.price,
            discount: 0,
            fees: 0,
        });
    }

    productSearch.value = "";
    selectedProducts.value = [];
};

const removeFromCart = (index) => {
    form.items.splice(index, 1);
};

const updateQuantity = (index, quantity) => {
    if (quantity <= 0) {
        removeFromCart(index);
    } else {
        form.items[index].quantity = quantity;
    }
};

const searchProducts = async () => {
    if (productSearch.value.length < 2) {
        selectedProducts.value = [];
        return;
    }

    loading.value = true;
    try {
        const response = await axios.get(route("pos.search-products"), {
            params: { q: productSearch.value },
        });
        selectedProducts.value = response.data.data;
    } catch (error) {
        console.error("Erro ao buscar produtos:", error);
    } finally {
        loading.value = false;
    }
};

const handleBarcodeInput = async () => {
    if (!barcodeInput.value) return;

    loading.value = true;

    try {
        const response = await axios.get(route("pos.barcode"), {
            params: { barcode: barcodeInput.value },
        });

        if (response.data.success) {
            addProductToCart(response.data.data);
        }
    } catch (error) {
        if (error.response?.status === 404) {
            toast.error("Produto não encontrado!");
        } else {
            console.error("Erro ao buscar produto:", error);
        }
    } finally {
        loading.value = false;
        barcodeInput.value = "";
    }
};

const openPaymentModal = () => {
    if (form.items.length === 0) {
        alert("Adicione pelo menos um produto ao carrinho!");
        return;
    }
    showPaymentModal.value = true;
};

const closePaymentModal = () => {
    showPaymentModal.value = false;
};

const processSale = () => {
    form.post(route("pos.process-sale"), {
        onSuccess: () => {
            // Limpar carrinho após venda
            form.reset();
            form.customer_id = props.defaultCustomer?.id || "";
            form.payment_method = "cash";
            form.installments = 1;
            closePaymentModal();
        },
    });
};

const clearCart = () => {
    if (confirm("Tem certeza que deseja limpar o carrinho?")) {
        form.items = [];
    }
};

const getItemTotal = (item) => {
    const quantity = parseLocaleNumber(item.quantity);
    const unitPrice = parseLocaleNumber(item.unit_price);
    const discount = parseLocaleNumber(item.discount || 0);
    const fees = parseLocaleNumber(item.fees || 0);
    return quantity * unitPrice - discount + fees;
};

// Focar no input de código de barras ao carregar
onMounted(() => {
    nextTick(() => {
        document.getElementById("barcode-input")?.focus();
    });
});

// Mostrar mensagem de sucesso se houver
onMounted(() => {
    if (props.success) {
        alert(
            `${props.success.message}\nPedido: ${
                props.success.order_id
            }\nTotal: ${formatCurrency(props.success.total)}`
        );
    }
});
</script>

<template>
    <Head title="PDV - Ponto de Venda" />

    <AppLayout>
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header">Código de Barras</div>
                    <div class="card-body">
                        <div class="input-group">
                            <input
                                id="barcode-input"
                                v-model="barcodeInput"
                                type="text"
                                class="form-control"
                                placeholder="Escaneie/Digite"
                                @keyup.enter="handleBarcodeInput"
                            />
                            <div class="input-group-append">
                                <button
                                    class="btn btn-default"
                                    @click="handleBarcodeInput"
                                    :disabled="loading"
                                >
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">Buscar Produtos</div>
                    <div class="card-body">
                        <div class="form-group">
                            <input
                                v-model="productSearch"
                                type="text"
                                class="form-control"
                                placeholder="Buscar"
                                @input="searchProducts"
                            />
                        </div>

                        <div
                            v-if="selectedProducts.length > 0"
                            class="product-list"
                        >
                            <div class="row">
                                <div
                                    v-for="product in selectedProducts"
                                    :key="product.id"
                                    class="col-md-6 col-lg-4 mb-2"
                                >
                                    <div
                                        class="card product-card h-100 d-flex flex-column"
                                        @click="addProductToCart(product)"
                                        style="cursor: pointer"
                                    >
                                        <div class="card-body p-2 flex-grow-1">
                                            <h6 class="card-title mb-1">
                                                {{ product.name }}
                                            </h6>
                                            <p class="card-text mb-0">
                                                <strong>{{
                                                    formatCurrency(
                                                        product.price
                                                    )
                                                }}</strong>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        Carrinho ({{ itemsCount }})
                        <button
                            v-if="form.items.length > 0"
                            class="btn btn-sm btn-danger"
                            @click="clearCart"
                        >
                            Esvaziar Carrinho
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div
                            v-if="form.items.length === 0"
                            class="text-center text-muted py-4"
                        >
                            <i class="fas fa-shopping-cart fa-2x mb-3"></i>
                            <p>Carrinho Vazio</p>
                        </div>

                        <div v-else>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Produto</th>
                                            <th width="120">Qtd</th>
                                            <th width="140">Preço Unit.</th>
                                            <th width="140">Total</th>
                                            <th width="50">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(item, index) in form.items"
                                            :key="index"
                                        >
                                            <td>
                                                <strong>{{
                                                    item.product_name
                                                }}</strong>
                                            </td>
                                            <td>
                                                <InputField
                                                    id="quantity-{{ index }}"
                                                    v-model.number="
                                                        item.quantity
                                                    "
                                                    type="number"
                                                    min="0.01"
                                                    step="0.01"
                                                    class="form-control-sm"
                                                    @change="
                                                        updateQuantity(
                                                            index,
                                                            item.quantity
                                                        )
                                                    "
                                                />
                                            </td>
                                            <td>
                                                <InputField
                                                    id="unit-price-{{ index }}"
                                                    v-model="item.unit_price"
                                                    maskType="currency"
                                                    class="form-control-sm"
                                                />
                                            </td>
                                            <td>
                                                <strong>{{
                                                    formatCurrency(
                                                        getItemTotal(item)
                                                    )
                                                }}</strong>
                                            </td>
                                            <td>
                                                <button
                                                    class="btn btn-sm btn-danger"
                                                    @click="
                                                        removeFromCart(index)
                                                    "
                                                >
                                                    Excluir
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header">Cliente</div>
                    <div class="card-body">
                        <Select2
                            v-model="form.customer_id"
                            :options="customers"
                            placeholder="Buscar Cliente"
                            :searchUrl="route('api.customers.search')"
                            required
                        />
                    </div>
                </div>

                <!-- Resumo da Venda -->
                <div class="card mb-3">
                    <div class="card-header">Resumo</div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong>{{ formatCurrency(subtotal) }}</strong>
                        </div>

                        <div class="form-group mb-2">
                            <label>Desconto:</label>
                            <InputField
                                id="discount-input"
                                v-model="form.discount"
                                maskType="currency"
                                placeholder="0,00"
                            />
                        </div>

                        <div class="form-group mb-3">
                            <label>Taxas:</label>
                            <InputField
                                id="fees-input"
                                v-model="form.fees"
                                maskType="currency"
                                placeholder="0,00"
                            />
                        </div>

                        <div class="d-flex justify-content-between">
                            <span>Total:</span>
                            <strong class="text-primary">{{
                                formattedTotal
                            }}</strong>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button
                            class="btn btn-success btn-block mb-2"
                            @click="openPaymentModal"
                            :disabled="form.items.length === 0"
                        >
                            <i class="fas fa-credit-card"></i>
                            &nbsp; Finalizar Venda
                        </button>

                        <button
                            class="btn btn-secondary btn-block"
                            @click="clearCart"
                            :disabled="form.items.length === 0"
                        >
                            <i class="fas fa-trash"></i>
                            &nbsp; Limpar Carrinho
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Pagamento -->
        <div
            v-if="showPaymentModal"
            class="modal fade show"
            style="display: block; background: rgba(0, 0, 0, 0.5)"
            @click.self="closePaymentModal"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Finalizar Venda</h5>
                        <button
                            type="button"
                            class="close"
                            @click="closePaymentModal"
                        >
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <h4>Total: {{ formattedTotal }}</h4>
                        </div>

                        <div class="form-group">
                            <label>Método de Pagamento:</label>
                            <select
                                v-model="form.payment_method"
                                class="form-control"
                            >
                                <option value="cash">Dinheiro</option>
                                <option value="card">Cartão</option>
                                <option value="pix">PIX</option>
                                <option value="installment">Parcelado</option>
                            </select>
                        </div>

                        <div
                            v-if="form.payment_method === 'installment'"
                            class="form-group"
                        >
                            <label>Número de Parcelas:</label>
                            <select
                                v-model="form.installments"
                                class="form-control"
                            >
                                <option v-for="i in 12" :key="i" :value="i">
                                    {{ i }}x de {{ formatCurrency(total / i) }}
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Observações:</label>
                            <textarea
                                v-model="form.observation"
                                class="form-control"
                                rows="3"
                                placeholder="Observações sobre a venda"
                            ></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            @click="closePaymentModal"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            class="btn btn-success"
                            @click="processSale"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing">
                                <i class="fas fa-spinner fa-spin"></i>
                                Processando...
                            </span>
                            <span v-else>
                                <i class="fas fa-check"></i>
                                Confirmar Venda
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.product-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.modal {
    z-index: 1050;
}
</style>
