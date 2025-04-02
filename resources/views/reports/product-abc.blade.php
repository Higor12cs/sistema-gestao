@extends('layouts.report-landscape-base')

@section('title', 'RELATÓRIO CURVA ABC - PRODUTOS')

@section('subtitle')
    Análise por {{ $analysisType === 'value' ? 'Valor (R$)' : 'Quantidade Vendida' }}
    @if (isset($startDate) && isset($endDate))
        | Período: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} a
        {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
    @endif
    @if ($filterDetails['brand'] || $filterDetails['section'] || $filterDetails['group'])
        | Filtros:
        @if ($filterDetails['brand'])
            Marca: {{ $filterDetails['brand'] }}
        @endif
        @if ($filterDetails['section'])
            | Seção: {{ $filterDetails['section'] }}
        @endif
        @if ($filterDetails['group'])
            | Grupo: {{ $filterDetails['group'] }}
        @endif
    @endif
@endsection

@section('content')
    <div class="summary">
        <div class="summary-title">RESUMO POR CLASSE</div>
        <div class="summary-grid">
            @foreach (['A', 'B', 'C'] as $class)
                <div class="summary-item">
                    <div class="summary-label">CLASSE {{ $class }}</div>
                    <div class="summary-value">{{ $totalsByClass[$class]['count'] }} produtos</div>
                    <div class="summary-value" style="font-size: 9pt;">
                        {{ number_format($totalsByClass[$class]['percent_count'], 1) }}% do total
                    </div>
                </div>
            @endforeach

            @foreach (['A', 'B', 'C'] as $class)
                <div class="summary-item">
                    <div class="summary-label">VALOR CLASSE {{ $class }}</div>
                    <div class="summary-value">R$ {{ number_format($totalsByClass[$class]['value'], 2, ',', '.') }}</div>
                    <div class="summary-value" style="font-size: 9pt;">
                        {{ number_format($totalsByClass[$class]['percent_value'], 1) }}% do faturamento
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="section">
        <div class="section-title">DISTRIBUIÇÃO DE PRODUTOS POR CLASSE</div>

        @php
            $classColors = [
                'A' => '#3498db', // Azul
                'B' => '#2ecc71', // Verde
                'C' => '#e74c3c', // Vermelho
            ];
        @endphp

        @if($analysisType === 'value')
            <div style="display: flex; height: 60px; width: 100%; margin-bottom: 20px; border-radius: 4px; overflow: hidden;">
                @foreach (['A', 'B', 'C'] as $class)
                    <div
                        style="height: 100%;width: {{ $totalsByClass[$class]['percent_value'] }}%;background-color: {{ $classColors[$class] }};display: flex;justify-content: center;align-items: center;color: white;font-weight: bold;flex-direction: column;">
                        <div>{{ $class }}</div>
                        <div style="font-size: 8pt;">{{ number_format($totalsByClass[$class]['percent_value'], 1) }}%</div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="display: flex; height: 60px; width: 100%; margin-bottom: 10px; border-radius: 4px; overflow: hidden;">
                @foreach (['A', 'B', 'C'] as $class)
                    <div
                        style="height: 100%;width: {{ $totalsByClass[$class]['percent_count'] }}%;background-color: {{ $classColors[$class] }};display: flex;justify-content: center;align-items: center;color: white;font-weight: bold;flex-direction: column;">
                        <div>{{ $class }}</div>
                        <div style="font-size: 8pt;">{{ number_format($totalsByClass[$class]['percent_count'], 1) }}%</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="section">
        <div class="section-title">LISTA DE PRODUTOS POR CLASSIFICAÇÃO</div>
        <table>
            <thead>
                <tr>
                    <th width="5%">Classe</th>
                    <th width="25%">Produto</th>
                    <th width="8%">SKU</th>
                    <th width="12%">Marca</th>
                    <th width="10%">Seção</th>
                    <th width="10%">Grupo</th>
                    <th width="8%" class="numeric">Qtde</th>
                    <th width="10%" class="numeric">Valor Total</th>
                    <th width="6%" class="numeric">% Item</th>
                    <th width="6%" class="numeric">% Acum.</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $index => $product)
                    <tr>
                        <td
                            style="
                                                                                                                                                                                                        background-color: {{ $classColors[$product['classification']] }};
                                                                                                                                                                                                        color: white;
                                                                                                                                                                                                        text-align: center;
                                                                                                                                                                                                        font-weight: bold;
                                                                                                                                                                                                    ">
                            {{ $product['classification'] }}
                        </td>
                        <td>{{ $product['product_name'] }}</td>
                        <td>{{ $product['sku'] }}</td>
                        <td>{{ $product['brand_name'] ?? 'N/A' }}</td>
                        <td>{{ $product['section_name'] ?? 'N/A' }}</td>
                        <td>{{ $product['group_name'] ?? 'N/A' }}</td>
                        <td class="numeric">{{ number_format($product['total_quantity'], 2, ',', '.') }}</td>
                        <td class="numeric">R$ {{ number_format($product['total_value'], 2, ',', '.') }}</td>
                        <td class="numeric">{{ number_format($product['percentage'], 2) }}%</td>
                        <td class="numeric">{{ number_format($product['accumulated_percentage'], 2) }}%</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="6">TOTAL</th>
                    <th class="numeric">{{ number_format($totalQuantity, 2, ',', '.') }}</th>
                    <th class="numeric">R$ {{ number_format($totalValue, 2, ',', '.') }}</th>
                    <th class="numeric">100.00%</th>
                    <th class="numeric"></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="section">
        <div class="section-title">OBSERVAÇÕES</div>
        <div class="observations">
            <p style="padding-bottom: 0.25rem;"><strong>Classificação ABC:</strong></p>
            <ul>
                <li><strong>Classe A:</strong> Produtos de alto giro que representam aproximadamente 80% do
                    faturamento/volume total.</li>
                <li><strong>Classe B:</strong> Produtos de médio giro que representam aproximadamente 15% do
                    faturamento/volume total.</li>
                <li><strong>Classe C:</strong> Produtos de baixo giro que representam aproximadamente 5% do
                    faturamento/volume total.</li>
            </ul>
            <p style="padding-top: 0.5rem; padding-bottom: 0.25rem;"><strong>Recomendações estratégicas:</strong></p>
            <ul>
                <li><strong>Para produtos Classe A:</strong> Prioridade em estoque, controle rigoroso, níveis de serviço
                    altos, negociação com fornecedores.</li>
                <li><strong>Para produtos Classe B:</strong> Monitoramento regular, estoque intermediário, potencial para
                    promoções.</li>
                <li><strong>Para produtos Classe C:</strong> Simplificar controles, reduzir variedade, avaliar
                    descontinuação de itens sem movimento.</li>
            </ul>
        </div>
    </div>
@endsection
