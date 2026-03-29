@extends('layouts.report')

@section('title', 'Curva ABC - Clientes')

@section('subtitle')
    Análise por {{ $analysisType === 'value' ? 'Valor (R$)' : 'Quantidade de Pedidos' }}
    @if ($startDate && $endDate)
        | Período: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} a
        {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
    @endif
@endsection

@section('content')
    <div class="summary">
        <h3>Resumo por Classe</h3>
        <table>
            @foreach (['A', 'B', 'C'] as $class)
                <tr>
                    <td><strong>Classe {{ $class }}</strong></td>
                    <td>{{ $totalsByClass[$class]['count'] }}</td>
                    <td>{{ number_format($totalsByClass[$class]['percent_count'], 1, ',', '.') }}%</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h3>Distribuição por Classe</h3>

        @if ($analysisType === 'value')
            <p>Distribuição por Valor:</p>
            @foreach (['A', 'B', 'C'] as $class)
                {{ $class }} - {{ number_format($totalsByClass[$class]['percent_value'], 1, ',', '.') }}%<br>
            @endforeach
        @else
            <p>Distribuição por Quantidade:</p>
            @foreach (['A', 'B', 'C'] as $class)
                {{ $class }} - {{ number_format($totalsByClass[$class]['percent_count'], 1, ',', '.') }}%<br>
            @endforeach
        @endif
    </div>

    <div class="section">
        <h3>Clientes por Classificação</h3>
        <table>
            <thead>
                <tr>
                    <th>Classe</th>
                    <th>Cliente</th>
                    <th class="numeric">Pedidos</th>
                    <th class="numeric">Valor Total</th>
                    <th class="numeric">% Item</th>
                    <th class="numeric">% Acum.</th>
                    <th class="numeric">Valor Médio</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customers as $customer)
                    <tr>
                        <td><strong>{{ $customer['classification'] }}</strong></td>
                        <td>{{ $customer['name'] }}</td>
                        <td class="numeric">{{ $customer['order_count'] }}</td>
                        <td class="numeric">R$ {{ number_format($customer['total_value'], 2, ',', '.') }}</td>
                        <td class="numeric">{{ number_format($customer['percentage'], 2, ',', '.') }}%</td>
                        <td class="numeric">{{ number_format($customer['accumulated_percentage'], 2, ',', '.') }}%</td>
                        <td class="numeric">R$
                            {{ number_format($customer['order_count'] > 0 ? $customer['total_value'] / $customer['order_count'] : 0, 2, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><strong>Total</strong></td>
                    <td class="numeric">{{ $totalOrders }}</td>
                    <td class="numeric">R$ {{ number_format($totalValue, 2, ',', '.') }}</td>
                    <td class="numeric">100.00%</td>
                    <td class="numeric">-</td>
                    <td class="numeric">R$
                        {{ number_format($totalOrders > 0 ? $totalValue / $totalOrders : 0, 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

@endsection

@section('footer-text', 'Relatório gerado em ' . now()->format('d/m/Y H:i:s'))
