@extends('layouts.report-base')

@section('title', 'Relatório Sintético de Pagáveis')

@section('content')
    <div class="header">
        <div class="report-title">RELATÓRIO SINTÉTICO DE PAGÁVEIS</div>
        <div class="report-subtitle">
            @php
                $dateTypeLabel = 'Emissão';
                if ($date_type === 'due_date') {
                    $dateTypeLabel = 'Vencimento';
                } elseif ($date_type === 'payment_date') {
                    $dateTypeLabel = 'Pagamento';
                }

                $statusLabel = 'Todos';
                if ($status === 'open') {
                    $statusLabel = 'Em Aberto';
                } elseif ($status === 'paid') {
                    $statusLabel = 'Pagos';
                }
            @endphp
            @if (isset($start_date) && isset($end_date))
                Período ({{ $dateTypeLabel }}): {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }} a
                {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}
            @else
                Período: Todo o histórico
            @endif
            | Status: {{ $statusLabel }}
        </div>
    </div>

    <div class="summary">
        <div class="summary-title">RESUMO GERAL</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">TOTAL DE PAGÁVEIS</div>
                <div class="summary-value">{{ $totalPayables }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">VALOR TOTAL</div>
                <div class="summary-value">R$ {{ number_format($totalAmount, 2, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">VALOR MÉDIO</div>
                <div class="summary-value">
                    R$ {{ number_format($avgAmount, 2, ',', '.') }}
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-label">TOTAL PAGO</div>
                <div class="summary-value">
                    R$ {{ number_format($totalPaid, 2, ',', '.') }}
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-label">TOTAL RESTANTE</div>
                <div class="summary-value">
                    R$ {{ number_format($totalRemaining, 2, ',', '.') }}
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-label">% PAGO</div>
                <div class="summary-value">
                    {{ $totalAmount > 0 ? number_format(($totalPaid / $totalAmount) * 100, 2) : 0 }}%
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">PAGÁVEIS POR PERÍODO
            ({{ $groupBy == 'day' ? 'DIA' : ($groupBy == 'week' ? 'SEMANA' : 'MÊS') }})</div>

        @if (count($summaryData) > 0)
            <table>
                <thead>
                    <tr>
                        <th width="20%">PERÍODO</th>
                        <th width="10%" class="numeric">QTDE</th>
                        <th width="15%" class="numeric">VALOR TOTAL</th>
                        <th width="15%" class="numeric">VALOR PAGO</th>
                        <th width="15%" class="numeric">RESTANTE</th>
                        <th width="10%" class="numeric">VALOR MÉDIO</th>
                        <th width="15%" class="numeric">% PAGO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($summaryData as $data)
                        <tr>
                            <td>{{ $data['label'] }}</td>
                            <td class="numeric">{{ $data['count'] }}</td>
                            <td class="numeric">R$ {{ number_format($data['total'], 2, ',', '.') }}</td>
                            <td class="numeric">R$ {{ number_format($data['paid'], 2, ',', '.') }}</td>
                            <td class="numeric">R$ {{ number_format($data['remaining'], 2, ',', '.') }}</td>
                            <td class="numeric">R$ {{ number_format($data['avg_amount'], 2, ',', '.') }}</td>
                            <td class="numeric">
                                {{ $data['total'] > 0 ? number_format(($data['paid'] / $data['total']) * 100, 2) : 0 }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>TOTAL</th>
                        <th class="numeric">{{ $totalPayables }}</th>
                        <th class="numeric">R$ {{ number_format($totalAmount, 2, ',', '.') }}</th>
                        <th class="numeric">R$ {{ number_format($totalPaid, 2, ',', '.') }}</th>
                        <th class="numeric">R$ {{ number_format($totalRemaining, 2, ',', '.') }}</th>
                        <th class="numeric">R$ {{ number_format($avgAmount, 2, ',', '.') }}</th>
                        <th class="numeric">
                            {{ $totalAmount > 0 ? number_format(($totalPaid / $totalAmount) * 100, 2) : 0 }}%</th>
                    </tr>
                </tfoot>
            </table>
        @else
            <p>Nenhum dado encontrado para o período selecionado.</p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">TOP 10 FORNECEDORES</div>

        @if (count($topSuppliers) > 0)
            <table>
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="35%">FORNECEDOR</th>
                        <th width="10%" class="numeric">QTDE</th>
                        <th width="15%" class="numeric">TOTAL</th>
                        <th width="15%" class="numeric">PAGO</th>
                        <th width="15%" class="numeric">RESTANTE</th>
                        <th width="5%" class="numeric">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topSuppliers as $index => $supplier)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $supplier['supplier'] }}</td>
                            <td class="numeric">{{ $supplier['count'] }}</td>
                            <td class="numeric">R$ {{ number_format($supplier['total'], 2, ',', '.') }}</td>
                            <td class="numeric">R$ {{ number_format($supplier['paid'], 2, ',', '.') }}</td>
                            <td class="numeric">R$ {{ number_format($supplier['remaining'], 2, ',', '.') }}</td>
                            <td class="numeric">
                                {{ $totalAmount > 0 ? number_format(($supplier['total'] / $totalAmount) * 100, 2) : 0 }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Nenhum dado de fornecedor encontrado para o período selecionado.</p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">TOP 10 PLANOS DE CONTAS</div>

        @if (count($topChartAccounts) > 0)
            <table>
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="10%">CÓDIGO</th>
                        <th width="25%">PLANO DE CONTAS</th>
                        <th width="10%" class="numeric">QTDE</th>
                        <th width="15%" class="numeric">TOTAL</th>
                        <th width="15%" class="numeric">PAGO</th>
                        <th width="15%" class="numeric">RESTANTE</th>
                        <th width="5%" class="numeric">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topChartAccounts as $index => $chartAccount)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $chartAccount['code'] }}</td>
                            <td>{{ $chartAccount['chart_account'] }}</td>
                            <td class="numeric">{{ $chartAccount['count'] }}</td>
                            <td class="numeric">R$ {{ number_format($chartAccount['total'], 2, ',', '.') }}</td>
                            <td class="numeric">R$ {{ number_format($chartAccount['paid'], 2, ',', '.') }}</td>
                            <td class="numeric">R$ {{ number_format($chartAccount['remaining'], 2, ',', '.') }}</td>
                            <td class="numeric">
                                {{ $totalAmount > 0 ? number_format(($chartAccount['total'] / $totalAmount) * 100, 2) : 0 }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Nenhum dado de plano de contas encontrado para o período selecionado.</p>
        @endif
    </div>
@endsection

@section('footer-text', 'Relatório gerado em ' . now()->format('d/m/Y H:i:s'))
