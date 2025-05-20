<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Relatórios de Pagáveis" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <h4>Relatórios de Pagáveis</h4>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Relatório Analítico</div>
                    <div class="card-body">
                        <p>
                            O relatório analítico exibe informações detalhadas
                            sobre cada pagável, incluindo fornecedor, valores,
                            datas e status de pagamento.
                        </p>
                        <div class="d-flex justify-content-end">
                            <Link
                                :href="route('reports.payables.analyticals')"
                                class="btn btn-primary"
                            >
                                <i class="fas fa-file-alt mr-2"></i>
                                Gerar Relatório Analítico
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Relatório Sintético</div>
                    <div class="card-body">
                        <p>
                            O relatório sintético exibe um resumo consolidado
                            dos pagáveis, agrupados por períodos e com
                            totalizadores por fornecedor e plano de contas.
                        </p>
                        <div class="d-flex justify-content-end">
                            <Link
                                :href="route('reports.payables.synthetics')"
                                class="btn btn-primary"
                            >
                                <i class="fas fa-chart-pie mr-2"></i>
                                Gerar Relatório Sintético
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
