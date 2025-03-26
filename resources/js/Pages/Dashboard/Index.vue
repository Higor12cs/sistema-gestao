<script setup>
import { ref, computed, onMounted, nextTick } from "vue";
import { usePage } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { Head } from "@inertiajs/vue3";
import DateRangePicker from "@/Components/DateRangePicker.vue";
import { Chart } from "chart.js";
import { registerables } from "chart.js";

Chart.register(...registerables);

const page = usePage();
const metrics = ref(
    page.props.metrics || {
        summary: {},
        salesData: {},
        purchasesData: {},
        financialData: {},
        topProducts: [],
        topCustomers: [],
        topSellers: [],
        salesByDay: [],
    }
);
const startDate = ref(page.props.startDate);
const endDate = ref(page.props.endDate);
const isLoading = ref(false);
const chartInstance = ref(null);
const salesChartCanvas = ref(null);

const formatCurrency = (value) => {
    const numValue = Number(value);

    if (isNaN(numValue) || value === null || value === undefined)
        return "R$ 0,00";

    if (numValue >= 1000000) {
        return `R$ ${(numValue / 1000000).toFixed(2)}M`;
    } else if (numValue >= 100000) {
        return `R$ ${(numValue / 1000).toFixed(0)}K`;
    } else if (numValue >= 10000) {
        return `R$ ${(numValue / 1000).toFixed(1)}K`;
    } else if (numValue >= 1000) {
        return `R$ ${(numValue / 1000).toFixed(2)}K`;
    } else {
        return `R$ ${numValue.toFixed(2).replace(".", ",")}`;
    }
};

const formatNumber = (value, decimals = 0) => {
    const numValue = Number(value);
    if (isNaN(numValue) || value === null || value === undefined) return "0";

    return new Intl.NumberFormat("pt-BR", {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(numValue);
};

const summary = computed(() => metrics.value.summary || {});
const salesData = computed(() => metrics.value.salesData || {});
const purchasesData = computed(() => metrics.value.purchasesData || {});
const financialData = computed(() => metrics.value.financialData || {});
const topProducts = computed(() => metrics.value.topProducts || []);
const topCustomers = computed(() => metrics.value.topCustomers || []);
const topSellers = computed(() => metrics.value.topSellers || []);
const salesByDay = computed(() => metrics.value.salesByDay || []);

const updateDashboard = () => {
    isLoading.value = true;
    router.get(
        route("dashboard.index"),
        {
            start_date: startDate.value,
            end_date: endDate.value,
        },
        {
            preserveState: true,
            onSuccess: () => {
                metrics.value = page.props.metrics;
                isLoading.value = false;
                nextTick(() => {
                    renderChart();
                });
            },
            onError: () => {
                isLoading.value = false;
            },
        }
    );
};

const handleDateChange = ({ startDate: newStartDate, endDate: newEndDate }) => {
    startDate.value = newStartDate;
    endDate.value = newEndDate;
    updateDashboard();
};

const renderChart = () => {
    if (chartInstance.value) {
        chartInstance.value.destroy();
    }

    if (!salesChartCanvas.value) {
        return;
    }

    try {
        const ctx = salesChartCanvas.value.getContext("2d");

        const labels = (salesByDay.value || []).map((day) => {
            const date = new Date(day.date);
            return date.toLocaleDateString("pt-BR", {
                day: "2-digit",
                month: "2-digit",
            });
        });

        const salesValues = (salesByDay.value || []).map(
            (day) => Number(day.sales) || 0
        );
        const expensesValues = (salesByDay.value || []).map(
            (day) => Number(day.expenses) || 0
        );

        chartInstance.value = new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Vendas",
                        data: salesValues,
                        backgroundColor: "rgba(40, 167, 69, 0.5)",
                        borderColor: "#28a745",
                        fill: true,
                        borderWidth: 2,
                        tension: 0.5,
                    },
                    {
                        label: "Compras",
                        data: expensesValues,
                        backgroundColor: "rgba(0, 123, 255, 0.5)",
                        borderColor: "#007bff",
                        fill: true,
                        borderWidth: 2,
                        tension: 0.5,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "top",
                    },
                    tooltip: {
                        mode: "index",
                        intersect: false,
                    },
                },
                hover: {
                    mode: "nearest",
                    intersect: true,
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                        },
                        ticks: {
                            callback: function (value) {
                                const numValue = Number(value);
                                if (isNaN(numValue)) return "R$ 0";

                                if (numValue >= 1000000) {
                                    return (
                                        "R$ " +
                                        (numValue / 1000000).toFixed(2) +
                                        "M"
                                    );
                                } else if (numValue >= 1000) {
                                    return (
                                        "R$ " +
                                        (numValue / 1000).toFixed(0) +
                                        "K"
                                    );
                                }
                                return "R$ " + numValue;
                            },
                        },
                    },
                },
            },
        });
    } catch (error) {
        console.error("Error initializing chart:", error);
    }
};

onMounted(() => {
    nextTick(() => {
        renderChart();
    });
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="mb-auto">
                <h4>Dashboard</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Dashboard' },
                    ]"
                />
            </div>
            <div class="ml-auto">
                <DateRangePicker
                    :start-date="startDate"
                    :end-date="endDate"
                    placeholder="Período:"
                    @apply="handleDateChange"
                />
            </div>
        </div>

        <h5 class="mt-3 mb-3">Indicadores Gerais</h5>
        <div class="row">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-success elevation-1 mr-1"
                        ><i class="fas fa-shopping-basket"></i
                    ></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Vendas</span>
                        <span class="info-box-number">{{
                            isLoading
                                ? "-"
                                : formatCurrency(salesData.totalSales || 0)
                        }}</span>
                        <span class="text-muted text-sm">Total no Período</span>
                    </div>
                    <div v-if="isLoading" class="overlay">
                        <i class="fas fa-sync-alt fa-spin"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-primary elevation-1 mr-1"
                        ><i class="fas fa-truck-loading"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Compras</span>
                        <span class="info-box-number">{{
                            isLoading
                                ? "-"
                                : formatCurrency(
                                      purchasesData.totalPurchases || 0
                                  )
                        }}</span>
                        <span class="text-muted text-sm">Total no Período</span>
                    </div>
                    <div v-if="isLoading" class="overlay">
                        <i class="fas fa-sync-alt fa-spin"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span
                        class="info-box-icon elevation-1"
                        :class="
                            (summary.balance || 0) >= 0
                                ? 'bg-success'
                                : 'bg-danger'
                        "
                    >
                        <i class="fas fa-money-bill-wave"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Saldo</span>
                        <span class="info-box-number">{{
                            isLoading
                                ? "-"
                                : formatCurrency(summary.balance || 0)
                        }}</span>
                        <span class="text-muted text-sm"
                            >Entradas - Saídas</span
                        >
                    </div>
                    <div v-if="isLoading" class="overlay">
                        <i class="fas fa-sync-alt fa-spin"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-info elevation-1 mr-1"
                        ><i class="fas fa-receipt"></i
                    ></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Ticket Médio</span>
                        <span class="info-box-number">{{
                            isLoading
                                ? "-"
                                : formatCurrency(salesData.averageTicket || 0)
                        }}</span>
                        <span class="text-muted text-sm"
                            >{{
                                isLoading ? "-" : salesData.orderCount || 0
                            }}
                            Pedidos</span
                        >
                    </div>
                    <div v-if="isLoading" class="overlay">
                        <i class="fas fa-sync-alt fa-spin"></i>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mt-3 mb-3">
            Vendas e Compras
            <i
                class="fas fa-sm fa-question-circle text-muted"
                title="Valores acumulados de vendas e compras realizadas no período filtrado."
            ></i>
        </h5>
        <div class="row">
            <div class="col-12">
                <div class="card position-relative">
                    <div class="card-body">
                        <div style="height: 350px; position: relative">
                            <canvas ref="salesChartCanvas"></canvas>
                        </div>
                    </div>
                    <div v-if="isLoading" class="overlay">
                        <i class="fas fa-sync-alt fa-spin"></i>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mt-3 mb-3">Financeiro</h5>
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card position-relative">
                    <div class="card-body">
                        <div
                            class="d-flex justify-content-between align-items-center"
                        >
                            <div>
                                <h6 class="text-muted mb-1">
                                    A Receber &nbsp;
                                    <i
                                        class="fas fa-sm fa-question-circle"
                                        title="Valores não vencidos e com vencimento inferior a data final do filtro."
                                    ></i>
                                </h6>
                                <h4 class="mb-0">
                                    {{
                                        isLoading
                                            ? "-"
                                            : formatCurrency(
                                                  financialData.pendingReceivables ||
                                                      0
                                              )
                                    }}
                                </h4>
                            </div>
                            <div class="bg-success p-3 rounded">
                                <i class="fas fa-dollar-sign text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div v-if="isLoading" class="overlay">
                        <i class="fas fa-sync-alt fa-spin"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card position-relative">
                    <div class="card-body">
                        <div
                            class="d-flex justify-content-between align-items-center"
                        >
                            <div>
                                <h6 class="text-muted mb-1">
                                    A Pagar &nbsp;
                                    <i
                                        class="fas fa-sm fa-question-circle"
                                        title="Valores não vencidos e com vencimento inferior a data final do filtro."
                                    ></i>
                                </h6>
                                <h4 class="mb-0">
                                    {{
                                        isLoading
                                            ? "-"
                                            : formatCurrency(
                                                  financialData.pendingPayables ||
                                                      0
                                              )
                                    }}
                                </h4>
                            </div>
                            <div class="bg-warning p-3 rounded">
                                <i class="fas fa-dollar-sign text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div v-if="isLoading" class="overlay">
                        <i class="fas fa-sync-alt fa-spin"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card position-relative">
                    <div class="card-body">
                        <div
                            class="d-flex justify-content-between align-items-center"
                        >
                            <div>
                                <h6 class="text-muted mb-1">
                                    Vencidos a Receber &nbsp;
                                    <i
                                        class="fas fa-sm fa-question-circle"
                                        title="Valores de recebíveis vencidos com a data de vencimento inferior a data de hoje."
                                    ></i>
                                </h6>
                                <h4 class="mb-0">
                                    {{
                                        isLoading
                                            ? "-"
                                            : formatCurrency(
                                                  financialData.overdueReceivables ||
                                                      0
                                              )
                                    }}
                                </h4>
                            </div>
                            <div class="bg-danger p-3 rounded">
                                <i
                                    class="fas fa-exclamation-triangle text-white"
                                ></i>
                            </div>
                        </div>
                    </div>
                    <div v-if="isLoading" class="overlay">
                        <i class="fas fa-sync-alt fa-spin"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card position-relative">
                    <div class="card-body">
                        <div
                            class="d-flex justify-content-between align-items-center"
                        >
                            <div>
                                <h6 class="text-muted mb-1">
                                    Vencidos a Pagar &nbsp;
                                    <i
                                        class="fas fa-sm fa-question-circle"
                                        title="Valores de pagáveis vencidos com a data de vencimento inferior a data de hoje."
                                    ></i>
                                </h6>
                                <h4 class="mb-0">
                                    {{
                                        isLoading
                                            ? "-"
                                            : formatCurrency(
                                                  financialData.overduePayables ||
                                                      0
                                              )
                                    }}
                                </h4>
                            </div>
                            <div class="bg-danger p-3 rounded">
                                <i
                                    class="fas fa-exclamation-triangle text-white"
                                ></i>
                            </div>
                        </div>
                    </div>
                    <div v-if="isLoading" class="overlay">
                        <i class="fas fa-sync-alt fa-spin"></i>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mt-3 mb-3">Top Produtos</h5>
        <div class="row">
            <div class="col-12">
                <div class="card position-relative">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th class="col-6">Produto</th>
                                        <th class="text-right col-3">
                                            Quantidade
                                        </th>
                                        <th class="text-right col-3">
                                            Receita
                                        </th>
                                    </tr>
                                </thead>
                                <tbody v-if="!isLoading">
                                    <tr
                                        v-for="product in topProducts"
                                        :key="product.id"
                                    >
                                        <td>{{ product.name }}</td>
                                        <td class="text-right">
                                            {{
                                                formatNumber(
                                                    product.total_quantity,
                                                    2
                                                )
                                            }}
                                        </td>
                                        <td class="text-right">
                                            {{
                                                formatCurrency(
                                                    product.total_revenue
                                                )
                                            }}
                                        </td>
                                    </tr>
                                    <tr v-if="!topProducts.length">
                                        <td colspan="3" class="text-center">
                                            Nenhum produto vendido no período.
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td
                                            colspan="3"
                                            class="text-center py-3"
                                        >
                                            <i
                                                class="fas fa-sync-alt fa-spin mr-2"
                                            ></i>
                                            Carregando...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mt-3 mb-3">Top Clientes</h5>
        <div class="row">
            <div class="col-12">
                <div class="card position-relative">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th class="col-6">Cliente</th>
                                        <th class="text-right col-3">
                                            Pedidos
                                        </th>
                                        <th class="text-right col-3">Total</th>
                                    </tr>
                                </thead>
                                <tbody v-if="!isLoading">
                                    <tr
                                        v-for="customer in topCustomers"
                                        :key="customer.id"
                                    >
                                        <td>{{ customer.name }}</td>
                                        <td class="text-right">
                                            {{ customer.order_count }}
                                        </td>
                                        <td class="text-right">
                                            {{
                                                formatCurrency(
                                                    customer.total_spent
                                                )
                                            }}
                                        </td>
                                    </tr>
                                    <tr v-if="!topCustomers.length">
                                        <td colspan="3" class="text-center">
                                            Nenhum cliente realizou compras no
                                            período.
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td
                                            colspan="3"
                                            class="text-center py-3"
                                        >
                                            <i
                                                class="fas fa-sync-alt fa-spin mr-2"
                                            ></i>
                                            Carregando...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mt-3 mb-3">Top Vendedores</h5>
        <div class="row">
            <div class="col-12">
                <div class="card position-relative">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th class="col-6">Vendedor</th>
                                        <th class="text-right col-3">
                                            Pedidos
                                        </th>
                                        <th class="text-right col-3">Total</th>
                                    </tr>
                                </thead>
                                <tbody v-if="!isLoading">
                                    <tr
                                        v-for="seller in topSellers"
                                        :key="seller.id"
                                    >
                                        <td>{{ seller.name }}</td>
                                        <td class="text-right">
                                            {{ seller.order_count }}
                                        </td>
                                        <td class="text-right">
                                            {{
                                                formatCurrency(
                                                    seller.total_spent
                                                )
                                            }}
                                        </td>
                                    </tr>
                                    <tr v-if="!topSellers.length">
                                        <td colspan="3" class="text-center">
                                            Nenhum vendedor realizou vendas no
                                            período.
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td
                                            colspan="3"
                                            class="text-center py-3"
                                        >
                                            <i
                                                class="fas fa-sync-alt fa-spin mr-2"
                                            ></i>
                                            Carregando...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
