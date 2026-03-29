@extends('layouts.report')

@section('title', 'Relatório Sintético de Pedidos')

@section('subtitle')
    @if ($start_date && $end_date)
        Período: {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }} a
        {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}
    @else
        Período: Todo o histórico
    @endif
@endsection

@section('content')

    <div class="summary">
        <h3>RESUMO GERAL</h3>
        <table>
            <tr>
                <td><strong>TOTAL DE PEDIDOS</strong></td>
                <td>{{ $totalOrders }}</td>
            </tr>
            <tr>
                <td><strong>VALOR TOTAL</strong></td>
                <td>R$ {{ number_format($totalSales, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>TICKET MÉDIO</strong></td>
                <td>R$ {{ number_format($avgTicket, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>PEDIDOS FINALIZADOS</strong></td>
                <td>{{ $orders->filter(function ($order) {
                        return $order->hasReceivables();
                    })->count() }}
                </td>
            </tr>
            <tr>
                <td><strong>PEDIDOS PENDENTES</strong></td>
                <td>{{ $orders->filter(function ($order) {
                        return !$order->hasReceivables();
                    })->count() }}
                </td>
            </tr>
            <tr>
                <td><strong>TOTAL DE PRODUTOS</strong></td>
                <td>{{ $totalItems }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>VENDAS POR PERÍODO ({{ $groupBy == 'day' ? 'DIA' : ($groupBy == 'week' ? 'SEMANA' : 'MÊS') }})</h3>

        @if (count($summaryData) > 0)
            <table>
                <thead>
                    <tr>
                        <th>PERÍODO</th>
                        <th class="numeric">PEDIDOS</th>
                        <th class="numeric">PRODUTOS</th>
                        <th class="numeric">TICKET MÉDIO</th>
                        <th class="numeric">TOTAL</th>
                        <th class="numeric">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($summaryData as $data)
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

@endsection

@section('footer-text', 'Relatório gerado em ' . now()->format('d/m/Y H:i:s'))

<div class="section">
    <div class="section-title">TOP 10 CLIENTES</div>

    @if (count($topCustomers) > 0)
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
                @foreach ($topCustomers as $index => $customer)
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

    @if (count($topSellers) > 0)
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
                @foreach ($topSellers as $index => $seller)
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
