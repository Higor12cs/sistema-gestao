<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { ref, onMounted } from "vue";
import axios from "axios";
import { formatCurrency, formatDate } from "@/Utils/Formatters.js";

const props = defineProps({
    order: Object,
});

const customer = ref(null);
const productInfo = ref({});

const fetchCustomerDetails = async () => {
    if (props.order.customer_id) {
        try {
            const response = await axios.get(route("api.customers.search"), {
                params: { ids: props.order.customer_id },
            });

            if (response.data.data.length > 0) {
                customer.value = response.data.data[0];
            }
        } catch (error) {
            console.error("Error fetching customer details:", error);
        }
    }
};

const fetchProductDetails = async () => {
    if (props.order.items.length > 0) {
        const productIds = props.order.items
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
    fetchCustomerDetails();
    fetchProductDetails();
});
</script>

<template>
    <Head title="Visualizar Pedido" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>
                    Visualizar Pedido #{{
                        String(order.sequential_id).padStart(6, "0")
                    }}
                </h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Pedidos', routeName: 'orders.index' },
                        { label: 'Visualizar' },
                    ]"
                />
            </div>

            <div class="d-flex">
                <div class="dropdown mr-2">
                    <button
                        class="btn btn-primary dropdown-toggle"
                        type="button"
                        id="printDropdown"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                    >
                        <i class="fas fa-print"></i>
                        &nbsp; Imprimir
                    </button>
                    <div class="dropdown-menu" aria-labelledby="printDropdown">
                        <a
                            class="dropdown-item"
                            :href="
                                route('orders.print', order.id) +
                                '?type=a4'
                            "
                            target="_blank"
                        >
                            Imprimir A4
                        </a>
                        <a
                            class="dropdown-item"
                            :href="
                                route('orders.print', order.id) +
                                '?type=thermal'
                            "
                            target="_blank"
                        >
                            Imprimir Térmica
                        </a>
                    </div>
                </div>

                <Link
                    :href="route('orders.index')"
                    class="btn btn-secondary mb-auto"
                >
                    <i class="fas fa-sm fa-arrow-left"></i>
                    &nbsp; Voltar
                </Link>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Detalhes do Pedido</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">Informações do Pedido</h5>
                                <p>
                                    <strong>Código:</strong>
                                    &nbsp; #{{
                                        String(order.sequential_id).padStart(
                                            6,
                                            "0"
                                        )
                                    }}
                                </p>
                                <p>
                                    <strong>Data Emissão:</strong>
                                    &nbsp;
                                    {{ formatDate(order.issue_date) }}
                                </p>
                                <p>
                                    <strong>Criado Por:</strong>
                                    &nbsp;
                                    {{ order.created_by?.name }}
                                </p>
                                <p>
                                    <strong>Data Criação:</strong>
                                    &nbsp;
                                    {{ formatDate(order.created_at) }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-3">Informações do Cliente</h5>
                                <div v-if="customer">
                                    <p>
                                        <strong>Cliente:</strong>
                                        &nbsp;
                                        {{ customer.name }}
                                    </p>
                                </div>
                                <div v-else>
                                    <p>Carregando informações do cliente...</p>
                                </div>
                            </div>
                        </div>

                        <hr />

                        <h5>Itens do Pedido</h5>
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
                                        v-for="(item, index) in order.items"
                                        :key="item.id"
                                    >
                                        <td>
                                            {{
                                                String(index + 1).padStart(
                                                    3,
                                                    "0"
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
                                            {{
                                                formatCurrency(item.unit_price)
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                formatCurrency(item.total_price)
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">Recebíveis</div>
                    <div class="card-body">
                        <div
                            v-if="
                                order.receivables &&
                                order.receivables.length > 0
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
                                            v-for="receivable in order.receivables"
                                            :key="receivable.id"
                                        >
                                            <td>
                                                {{
                                                    String(
                                                        receivable.sequential_id
                                                    ).padStart(6, "0")
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    receivable.payment_method
                                                        ?.name || "N/A"
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    formatDate(
                                                        receivable.due_date
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    formatCurrency(
                                                        receivable.total_amount
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <span
                                                    class="badge"
                                                    :class="{
                                                        'bg-success':
                                                            receivable.status ===
                                                            'paid',
                                                        'bg-warning':
                                                            receivable.status ===
                                                            'partial',
                                                        'bg-danger':
                                                            receivable.status ===
                                                            'pending',
                                                    }"
                                                >
                                                    {{
                                                        receivable.status ===
                                                        "paid"
                                                            ? "Pago"
                                                            : receivable.status ===
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
                                Este pedido ainda não possui recebíveis.
                                <Link
                                    :href="
                                        route(
                                            'orders.create-receivables',
                                            order.id
                                        )
                                    "
                                    class="btn btn-sm btn-primary ml-2"
                                >
                                    Criar Recebíveis
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Resumo do Pedido</div>
                    <div class="card-body px-0">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td><strong>Subtotal:</strong></td>
                                    <td class="text-right">
                                        {{
                                            formatCurrency(
                                                Number(order.total_price) +
                                                    Number(order.discount) -
                                                    Number(order.fees)
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Descontos (-):</strong></td>
                                    <td class="text-right">
                                        {{ formatCurrency(order.discount) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Taxas (+):</strong></td>
                                    <td class="text-right">
                                        {{ formatCurrency(order.fees) }}
                                    </td>
                                </tr>
                                <!-- <tr>
                                    <td><strong>Custo Total:</strong></td>
                                    <td class="text-right">
                                        {{ formatCurrency(order.total_cost) }}
                                    </td>
                                </tr> -->
                                <tr class="table-active">
                                    <td><strong>Total:</strong></td>
                                    <td class="text-right">
                                        <strong>{{
                                            formatCurrency(order.total_price)
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
                        <p v-if="order.observation">{{ order.observation }}</p>
                        <p v-else class="text-muted">
                            Nenhuma observação registrada.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
