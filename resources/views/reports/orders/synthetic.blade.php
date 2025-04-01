{{-- reports/orders/synthetic.blade.php --}}
@extends('layouts.report-base')

@section('title', 'Relatório Sintético de Pedidos')

@section('content')
    <div class="header">
        <div class="report-title">RELATÓRIO SINTÉTICO DE PEDIDOS</div>
        <div class="report-subtitle">
            @if(isset($start_date) && isset($end_date))
                Período: {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }} a
                {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}
            @else
                Período: Todo o histórico
            @endif
        </div>
    </div>

    <div class="summary">
        <div class="summary-title">RESUMO GERAL</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">TOTAL DE PEDIDOS</div>
                <div class="summary-value">{{ $totalOrders }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">VALOR TOTAL</div>
                <div class="summary-value">R$ {{ number_format($totalSales, 2, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">TICKET MÉDIO</div>
                <div class="summary-value">
                    R$ {{ number_format($avgTicket, 2, ',', '.') }}
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-label">PEDIDOS FINALIZADOS</div>
                <div class="summary-value">
                    {{ $orders->filter(function ($order) {
        return $order->hasReceivables(); })->count() }}
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-label">PEDIDOS PENDENTES</div>
                <div class="summary-value">
                    {{ $orders->filter(function ($order) {
        return !$order->hasReceivables(); })->count() }}
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-label">TOTAL DE PRODUTOS</div>
                <div class="summary-value">
                    {{ $totalItems }}
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">VENDAS POR PERÍODO
            ({{ $groupBy == 'day' ? 'DIA' : ($groupBy == 'week' ? 'SEMANA' : 'MÊS') }})</div>

        @if(count($summaryData) > 0)
            <table>
                <thead>
                    <tr>
                        <th width="25%">PERÍODO</th>
                        <th width="15%" class="numeric">PEDIDOS</th>
                        <th width="15%" class="numeric">PRODUTOS</th>
                        <th width="15%" class="numeric">TICKET MÉDIO</th>
                        <th width="15%" class="numeric">TOTAL</th>
                        <th width="15%" class="numeric">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summaryData as $data)
                        <tr>
                            <td>{{ $data['label'] }}</td>
                            <td class="numeric">{{ $data['count'] }}</td>
                            <td class="numeric">{{ $data['items_count'] }}</td>
                            <td class="numeric">R$ {{ number_format($data['average'], 2, ',', '.') }}</td>
                            <td class="numeric">R$ {{ number_format($data['total'], 2, ',', '.') }}</td>
                            <td class="numeric">{{ number_format(($data['total'] / $totalSales) * 100, 2) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>TOTAL</th>
                        <th class="numeric">{{ $totalOrders }}</th>
                        <th class="numeric">{{ $totalItems }}</th>
                        <th class="numeric">R$ {{ number_format($avgTicket, 2, ',', '.') }}</th>
                        <th class="numeric">R$ {{ number_format($totalSales, 2, ',', '.') }}</th>
                        <th class="numeric">100.00%</th>
                    </tr>
                </tfoot>
            </table>
        @else
            <p>Nenhum dado encontrado para o período selecionado.</p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">TOP 10 CLIENTES</div>

        @if(count($topCustomers) > 0)
            <table>
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="55%">CLIENTE</th>
                        <th width="15%" class="numeric">PEDIDOS</th>
                        <th width="15%" class="numeric">TOTAL</th>
                        <th width="10%" class="numeric">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topCustomers as $index => $customer)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $customer['customer'] }}</td>
                            <td class="numeric">{{ $customer['count'] }}</td>
                            <td class="numeric">R$ {{ number_format($customer['total'], 2, ',', '.') }}</td>
                            <td class="numeric">{{ number_format(($customer['total'] / $totalSales) * 100, 2) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Nenhum dado de cliente encontrado para o período selecionado.</p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">TOP 10 VENDEDORES</div>

        @if(count($topSellers) > 0)
            <table>
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="55%">VENDEDOR</th>
                        <th width="15%" class="numeric">PEDIDOS</th>
                        <th width="15%" class="numeric">TOTAL</th>
                        <th width="10%" class="numeric">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topSellers as $index => $seller)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $seller['seller'] }}</td>
                            <td class="numeric">{{ $seller['count'] }}</td>
                            <td class="numeric">R$ {{ number_format($seller['total'], 2, ',', '.') }}</td>
                            <td class="numeric">{{ number_format(($seller['total'] / $totalSales) * 100, 2) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Nenhum dado de vendedor encontrado para o período selecionado.</p>
        @endif
    </div>
@endsection

@section('footer-text', 'Relatório gerado em ' . now()->format('d/m/Y H:i:s'))
