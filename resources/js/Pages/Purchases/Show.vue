<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { ref, onMounted } from "vue";
import axios from "axios";
import { formatCurrency, formatDate } from "@/Utils/Formatters.js";

const props = defineProps({
    purchase: Object,
});

const supplier = ref(null);
const productInfo = ref({});

const fetchSupplierDetails = async () => {
    if (props.purchase.supplier_id) {
        try {
            const response = await axios.get(route("api.suppliers.search"), {
                params: { ids: props.purchase.supplier_id },
            });

            if (response.data.data.length > 0) {
                supplier.value = response.data.data[0];
            }
        } catch (error) {
            console.error("Error fetching supplier details:", error);
        }
    }
};

const fetchProductDetails = async () => {
    if (props.purchase.items.length > 0) {
        const productIds = props.purchase.items
            .map((item) => item.product_id)
            .join(",");

        try {
            const response = await axios.get(route("api.products.search"), {
                params: { ids: productIds },
            });

            response.data.data.forEach((product) => {
                productInfo.value[product.id] = product;
            });
        } catch (error) {
            console.error("Error fetching product details:", error);
        }
    }
};

onMounted(() => {
    fetchSupplierDetails();
    fetchProductDetails();
});
</script>

<template>
    <Head title="Visualizar Compra" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>
                    Visualizar Compra #{{
                        String(purchase.id).padStart(6, "0")
                    }}
                </h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Compras', routeName: 'purchases.index' },
                        { label: 'Visualizar' },
                    ]"
                />
            </div>

            <Link
                :href="route('purchases.index')"
                class="btn btn-secondary mb-auto"
            >
                <i class="fas fa-sm fa-arrow-left"></i>
                &nbsp; Voltar
            </Link>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Detalhes da Compra</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">Informações da Compra</h5>
                                <p>
                                    <strong>Código:</strong>
                                    &nbsp; #{{
                                        String(purchase.id).padStart(6, "0")
                                    }}
                                </p>
                                <p>
                                    <strong>Data Emissão:</strong>
                                    &nbsp;
                                    {{ formatDate(purchase.issue_date) }}
                                </p>
                                <p>
                                    <strong>Criado Por:</strong>
                                    &nbsp;
                                    {{ purchase.created_by?.name }}
                                </p>
                                <p>
                                    <strong>Data Criação:</strong>
                                    &nbsp;
                                    {{ formatDate(purchase.created_at) }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-3">Informações do Fornecedor</h5>
                                <div v-if="supplier">
                                    <p>
                                        <strong>Fornecedor:</strong>
                                        &nbsp;
                                        {{ supplier.name }}
                                    </p>
                                </div>
                                <div v-else>
                                    <p>
                                        Carregando informações do fornecedor...
                                    </p>
                                </div>
                            </div>
                        </div>

                        <hr />

                        <h5>Itens da Compra</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr class="text-nowrap">
                                        <th>Item</th>
                                        <th>Produto</th>
                                        <th>Quantidade</th>
                                        <th>Preço Unit.</th>
                                        <th>Preço Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(item, index) in purchase.items"
                                        :key="item.id"
                                    >
                                        <td>
                                            {{
                                                String(index + 1).padStart(
                                                    3,
                                                    "0",
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                productInfo[item.product_id]
                                                    ?.name || "Carregando..."
                                            }}
                                        </td>
                                        <td>{{ item.quantity }}</td>
                                        <td>
                                            {{ formatCurrency(item.unit_cost) }}
                                        </td>
                                        <td>
                                            {{
                                                formatCurrency(item.total_cost)
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">Pagáveis</div>
                    <div class="card-body">
                        <div
                            v-if="
                                purchase.payables &&
                                purchase.payables.length > 0
                            "
                        >
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Método de Pagamento</th>
                                            <th>Vencimento</th>
                                            <th>Valor</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="payable in purchase.payables"
                                            :key="payable.id"
                                        >
                                            <td>
                                                {{
                                                    String(payable.id).padStart(
                                                        6,
                                                        "0",
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    payable.payment_method
                                                        ?.name || "N/A"
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    formatDate(payable.due_date)
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    formatCurrency(
                                                        payable.total_amount,
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <span
                                                    class="badge"
                                                    :class="{
                                                        'bg-success':
                                                            payable.status ===
                                                            'paid',
                                                        'bg-warning':
                                                            payable.status ===
                                                            'partial',
                                                        'bg-danger':
                                                            payable.status ===
                                                            'pending',
                                                    }"
                                                >
                                                    {{
                                                        payable.status ===
                                                        "paid"
                                                            ? "Pago"
                                                            : payable.status ===
                                                                "partial"
                                                              ? "Parcial"
                                                              : "Pendente"
                                                    }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div v-else>
                            <div class="alert alert-info">
                                Esta compra ainda não possui pagáveis.
                                <Link
                                    :href="
                                        route(
                                            'purchases.create-payables',
                                            purchase.id,
                                        )
                                    "
                                    class="btn btn-sm btn-primary ml-2"
                                >
                                    Criar Pagáveis
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Resumo da Compra</div>
                    <div class="card-body px-0">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td><strong>Subtotal:</strong></td>
                                    <td class="text-right">
                                        {{
                                            formatCurrency(
                                                Number(purchase.total_cost) +
                                                    Number(purchase.discount) -
                                                    Number(purchase.fees),
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Descontos (-):</strong></td>
                                    <td class="text-right">
                                        {{ formatCurrency(purchase.discount) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Taxas (+):</strong></td>
                                    <td class="text-right">
                                        {{ formatCurrency(purchase.fees) }}
                                    </td>
                                </tr>
                                <tr class="table-active">
                                    <td><strong>Total:</strong></td>
                                    <td class="text-right">
                                        <strong>{{
                                            formatCurrency(purchase.total_cost)
                                        }}</strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">Observações</div>
                    <div class="card-body">
                        <p v-if="purchase.observation">
                            {{ purchase.observation }}
                        </p>
                        <p v-else class="text-muted">
                            Nenhuma observação registrada.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
