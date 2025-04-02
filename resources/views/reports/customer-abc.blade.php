@extends('layouts.report-landscape-base')

@section('title', 'RELATÓRIO CURVA ABC - CLIENTES')

@section('subtitle')
    Análise por {{ $analysisType === 'value' ? 'Valor (R$)' : 'Quantidade de Pedidos' }}
    @if (isset($startDate) && isset($endDate))
        | Período: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} a
        {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
    @endif
@endsection

@section('content')
    <div class="summary">
        <div class="summary-title">RESUMO POR CLASSE</div>
        <div class="summary-grid">
            @foreach (['A', 'B', 'C'] as $class)
                <div class="summary-item">
                    <div class="summary-label">CLASSE {{ $class }}</div>
                    <div class="summary-value">{{ $totalsByClass[$class]['count'] }} clientes</div>
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
        <div class="section-title">DISTRIBUIÇÃO DE CLIENTES POR CLASSE</div>
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
        <div class="section-title">LISTA DE CLIENTES POR CLASSIFICAÇÃO</div>
        <table>
            <thead>
                <tr>
                    <th width="5%">Classe</th>
                    <th width="35%">Cliente</th>
                    <th width="10%" class="numeric">Pedidos</th>
                    <th width="15%" class="numeric">Valor Total</th>
                    <th width="10%" class="numeric">% Item</th>
                    <th width="10%" class="numeric">% Acum.</th>
                    <th width="15%" class="numeric">Valor Médio</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customers as $index => $customer)
                    <tr>
                        <td
                            style="background-color: {{ $classColors[$customer['classification']] }};color: white;text-align: center;font-weight: bold;">
                            {{ $customer['classification'] }}
                        </td>
                        <td>{{ $customer['name'] }} | {{ str_pad($customer['sequential_id'], 6, '0', STR_PAD_LEFT) }}</td>
                        <td class="numeric">{{ $customer['order_count'] }}</td>
                        <td class="numeric">R$ {{ number_format($customer['total_value'], 2, ',', '.') }}</td>
                        <td class="numeric">{{ number_format($customer['percentage'], 2) }}%</td>
                        <td class="numeric">{{ number_format($customer['accumulated_percentage'], 2) }}%</td>
                        <td class="numeric">
                            R$
                            {{ number_format($customer['order_count'] > 0 ? $customer['total_value'] / $customer['order_count'] : 0, 2, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2">TOTAL</th>
                    <th class="numeric">{{ $totalOrders }}</th>
                    <th class="numeric">R$ {{ number_format($totalValue, 2, ',', '.') }}</th>
                    <th class="numeric">100.00%</th>
                    <th class="numeric"></th>
                    <th class="numeric">
                        R$ {{ number_format($totalOrders > 0 ? $totalValue / $totalOrders : 0, 2, ',', '.') }}
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="section">
        <div class="section-title">OBSERVAÇÕES</div>
        <div class="observations">
            <p style="padding-bottom: 0.25rem;"><strong>Classificação ABC:</strong></p>
            <ul>
                <li><strong>Classe A:</strong> Clientes de alto valor que representam aproximadamente 80% do faturamento
                    total.</li>
                <li><strong>Classe B:</strong> Clientes de médio valor que representam aproximadamente 15% do faturamento
                    total.</li>
                <li><strong>Classe C:</strong> Clientes de baixo valor que representam aproximadamente 5% do faturamento
                    total.</li>
            </ul>
            <p style="padding-top: 0.5rem; padding-bottom: 0.25rem;"><strong>Recomendações estratégicas:</strong></p>
            <ul>
                <li><strong>Para clientes Classe A:</strong> Foco em retenção, programa de relacionamento, atendimento
                    personalizado.</li>
                <li><strong>Para clientes Classe B:</strong> Estratégias de upsell, cross-sell e incentivos para aumentar
                    frequência de compra.</li>
                <li><strong>Para clientes Classe C:</strong> Automação do atendimento, campanhas de reativação e promoções
                    específicas.</li>
            </ul>
        </div>
    </div>
@endsection
