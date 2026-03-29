@extends('layouts.report')

@section('title', 'Curva ABC - Produtos')

@section('subtitle')
    Análise por {{ $analysisType === 'value' ? 'Valor (R$)' : 'Quantidade Vendida' }}
    @if ($startDate && $endDate)
        | Período: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} a
        {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
    @endif
    @if ($filterDetails['brand'] || $filterDetails['section'] || $filterDetails['group'])
        | Filtros:
        @if ($filterDetails['brand'])
            Marca: {{ $filterDetails['brand'] }}
        @endif
        @if ($filterDetails['section'])
            Seção: {{ $filterDetails['section'] }}
        @endif
        @if ($filterDetails['group'])
            Grupo: {{ $filterDetails['group'] }}
        @endif
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
        <h3>Produtos por Classificação</h3>
        <table>
            <thead>
                <tr>
                    <th>Classe</th>
                    <th>Produto</th>
                    <th>SKU</th>
                    <th>Marca</th>
                    <th>Seção</th>
                    <th>Grupo</th>
                    <th class="numeric">Qtde</th>
                    <th class="numeric">Valor</th>
                    <th class="numeric">%</th>
                    <th class="numeric">Acum</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td><strong>{{ $product['classification'] }}</strong></td>
                        <td>{{ $product['product_name'] }}</td>
                        <td>{{ $product['sku'] }}</td>
                        <td>{{ $product['brand_name'] ?? 'N/A' }}</td>
                        <td>{{ $product['section_name'] ?? 'N/A' }}</td>
                        <td>{{ $product['group_name'] ?? 'N/A' }}</td>
                        <td class="numeric">{{ number_format($product['total_quantity'], 2, ',', '.') }}</td>
                        <td class="numeric">R$ {{ number_format($product['total_value'], 2, ',', '.') }}</td>
                        <td class="numeric">{{ number_format($product['percentage'], 2, ',', '.') }}%</td>
                        <td class="numeric">{{ number_format($product['accumulated_percentage'], 2, ',', '.') }}%</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="6">Total</th>
                    <th class="numeric">{{ number_format($totalQuantity, 2, ',', '.') }}</th>
                    <th class="numeric">R$ {{ number_format($totalValue, 2, ',', '.') }}</th>
                    <th class="numeric">100.00%</th>
                    <th class="numeric">-</th>
                </tr>
            </tfoot>
        </table>
    </div>

@endsection

@section('footer-text', 'Relatório gerado em ' . now()->format('d/m/Y H:i:s'))
