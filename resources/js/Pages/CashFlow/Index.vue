<script setup>
import { ref, computed, onMounted, nextTick } from "vue";
import { usePage } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { Head } from "@inertiajs/vue3";
import DateRangePicker from "@/Components/DateRangePicker.vue";
import DailyDetailsModal from "@/Components/CashFlow/DailyDetailsModal.vue";
import { Chart } from "chart.js";
import { registerables } from "chart.js";

Chart.register(...registerables);

const page = usePage();
const metrics = ref(
    page.props.metrics || {
        cashFlowData: [],
        summaryData: {},
        accountBalances: [],
    }
);
const startDate = ref(page.props.startDate);
const endDate = ref(page.props.endDate);
const isLoading = ref(false);
const chartInstance = ref(null);
const cashFlowChartCanvas = ref(null);
const selectedDay = ref(null);
const showDailyDetailsModal = ref(false);
const dailyDetails = ref({
    transactions: [],
    receivables: [],
    payables: [],
    date: null,
});

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

const cashFlowData = computed(() => metrics.value.cashFlowData || []);
const summaryData = computed(() => metrics.value.summaryData || {});
const accountBalances = computed(() => metrics.value.accountBalances || []);

const updateDashboard = () => {
    isLoading.value = true;
    router.get(
        route("cash-flow.index"),
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

    if (!cashFlowChartCanvas.value) {
        return;
    }

    try {
        const ctx = cashFlowChartCanvas.value.getContext("2d");

        const labels = (cashFlowData.value || []).map((day) => {
            const [year, month, day_num] = day.date.split("-");
            return `${day_num}/${month}`;
        });

        const receivablesData = (cashFlowData.value || []).map(
            (day) => Number(day.expected_income) || 0
        );
        const payablesData = (cashFlowData.value || []).map(
            (day) => Number(day.expected_expense) || 0
        );
        const balanceData = (cashFlowData.value || []).map(
            (day) => Number(day.projected_balance) || 0
        );

        chartInstance.value = new Chart(ctx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Recebíveis",
                        data: receivablesData,
                        backgroundColor: "rgba(40, 167, 69, 0.5)",
                        borderColor: "#28a745",
                        borderWidth: 1,
                        order: 2,
                    },
                    {
                        label: "Pagáveis",
                        data: payablesData,
                        backgroundColor: "rgba(220, 53, 69, 0.5)",
                        borderColor: "#dc3545",
                        borderWidth: 1,
                        order: 2,
                    },
                    {
                        label: "Saldo Projetado",
                        data: balanceData,
                        type: "line",
                        borderColor: "rgba(0, 123, 255, 1)",
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        order: 1,
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
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || "";
                                if (label) {
                                    label += ": ";
                                }
                                if (context.parsed.y !== null) {
                                    label += formatCurrency(context.parsed.y);
                                }
                                return label;
                            },
                        },
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
                        stacked: true,
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                        },
                        stacked: false,
                        ticks: {
                            callback: function (value) {
                                return formatCurrency(value);
                            },
                        },
                    },
                },
                onClick: (event, elements) => {
                    if (elements && elements.length > 0) {
                        const index = elements[0].index;
                        if (cashFlowData.value[index]) {
                            handleDayClick(cashFlowData.value[index]);
                        }
                    }
                },
            },
        });
    } catch (error) {
        console.error("Error initializing chart:", error);
    }
};

const handleDayClick = (dayData) => {
    selectedDay.value = dayData;
    fetchDailyDetails(dayData.date);
};

const fetchDailyDetails = (date) => {
    isLoading.value = true;

    fetch(route("cash-flow.daily-details", { date: date }))
        .then((response) => response.json())
        .then((data) => {
            dailyDetails.value = data;
            showDailyDetailsModal.value = true;
            isLoading.value = false;
        })
        .catch((error) => {
            console.error("Error fetching daily details:", error);
            isLoading.value = false;
        });
};

const closeDailyDetailsModal = () => {
    showDailyDetailsModal.value = false;
};

const formatDate = (dateString) => {
    if (dateString.includes('T')) {
        dateString = dateString.split('T')[0];
    }

    const [year, month, day] = dateString.split('-');

    return `${day}/${month}/${year}`;
};

onMounted(() => {
    nextTick(() => {
        renderChart();
    });
});
</script>

<template>
    <Head title="Fluxo de Caixa" />

    <AuthenticatedLayout>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="mb-auto">
                <h4>Fluxo de Caixa</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Fluxo de Caixa' },
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

        <h5 class="mt-3 mb-3">Resumo Financeiro</h5>
        <div class="row">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-success elevation-1 mr-1"
                        ><i class="fas fa-money-bill-wave-alt"></i
                    ></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Recebíveis</span>
                        <span class="info-box-number">{{
                            isLoading
                                ? "-"
                                : formatCurrency(
                                      summaryData.totalReceivables || 0
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
                    <span class="info-box-icon bg-danger elevation-1 mr-1"
                        ><i class="fas fa-file-invoice-dollar"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pagáveis</span>
                        <span class="info-box-number">{{
                            isLoading
                                ? "-"
                                : formatCurrency(summaryData.totalPayables || 0)
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
                            (summaryData.expectedBalance || 0) >= 0
                                ? 'bg-success'
                                : 'bg-danger'
                        "
                    >
                        <i class="fas fa-chart-line"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Saldo Projetado</span>
                        <span class="info-box-number">{{
                            isLoading
                                ? "-"
                                : formatCurrency(
                                      summaryData.expectedBalance || 0
                                  )
                        }}</span>
                        <span class="text-muted text-sm"
                            >Saldo + Receber - Pagar</span
                        >
                    </div>
                    <div v-if="isLoading" class="overlay">
                        <i class="fas fa-sync-alt fa-spin"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-warning elevation-1 mr-1"
                        ><i class="fas fa-exclamation-triangle"></i
                    ></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Vencidos</span>
                        <span class="info-box-number">{{
                            isLoading
                                ? "-"
                                : formatCurrency(
                                      summaryData.overdueReceivables -
                                          summaryData.overduePayables || 0
                                  )
                        }}</span>
                        <span class="text-muted text-sm">Receber - Pagar</span>
                    </div>
                    <div v-if="isLoading" class="overlay">
                        <i class="fas fa-sync-alt fa-spin"></i>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mt-3 mb-3">
            Fluxo de Caixa Projetado
            <i
                class="fas fa-sm fa-question-circle text-muted"
                title="Clique em uma data no gráfico para ver os detalhes dos lançamentos daquele dia."
            ></i>
        </h5>
        <div class="row">
            <div class="col-12">
                <div class="card position-relative">
                    <div class="card-body">
                        <div style="height: 350px; position: relative">
                            <canvas ref="cashFlowChartCanvas"></canvas>
                        </div>
                    </div>
                    <div v-if="isLoading" class="overlay">
                        <i class="fas fa-sync-alt fa-spin"></i>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mt-3 mb-3">Saldos das Contas</h5>
        <div class="row">
            <div
                v-for="account in accountBalances"
                :key="account.id"
                class="col-lg-3 col-md-6 mb-4"
            >
                <div class="card position-relative">
                    <div class="card-body">
                        <div
                            class="d-flex justify-content-between align-items-center"
                        >
                            <div>
                                <h6 class="text-muted mb-1">
                                    {{ account.name }}
                                </h6>
                                <h4 class="mb-0">
                                    {{
                                        formatCurrency(account.current_balance)
                                    }}
                                </h4>
                            </div>
                            <div
                                :class="`bg-${
                                    account.current_balance >= 0
                                        ? 'info'
                                        : 'warning'
                                } p-3 rounded`"
                            >
                                <i class="fas fa-wallet text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div v-if="isLoading" class="overlay">
                        <i class="fas fa-sync-alt fa-spin"></i>
                    </div>
                </div>
            </div>
        </div>

        <DailyDetailsModal
            v-if="showDailyDetailsModal"
            :visible="showDailyDetailsModal"
            :transactions="dailyDetails.transactions || []"
            :receivables="dailyDetails.receivables || []"
            :payables="dailyDetails.payables || []"
            :date="dailyDetails.date"
            :is-loading="isLoading"
            @close="closeDailyDetailsModal"
        />
    </AuthenticatedLayout>
</template>
