<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Relatório Sintético de Pedidos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/jetbrains-mono@4.5.0/index.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #ecf0f1;
            --accent-color: #3498db;
            --danger-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --text-color: #333;
            --border-color: #ddd;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* Page setup */
        @page {
            margin: 10mm;
        }

        body {
            font-family: 'JetBrains Mono', monospace;
            font-size: 9pt;
            line-height: 1.4;
            color: var(--text-color);
            width: 210mm;
            margin: 0 auto;
        }

        .container {
            width: 100%;
        }

        /* Header styling */
        .header {
            text-align: center;
            padding-bottom: 5mm;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 5mm;
        }

        .report-title {
            font-size: 16pt;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 2mm;
        }

        .report-subtitle {
            font-size: 10pt;
            color: var(--text-color);
            margin-bottom: 2mm;
        }

        /* Summary section */
        .summary {
            background-color: var(--secondary-color);
            padding: 4mm;
            margin-bottom: 8mm;
            border-radius: 2mm;
        }

        .summary-title {
            font-size: 12pt;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 3mm;
            text-align: center;
        }

        .summary-grid {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .summary-item {
            width: 30%;
            text-align: center;
            margin-bottom: 3mm;
        }

        .summary-label {
            font-size: 8pt;
            color: var(--text-color);
            margin-bottom: 1mm;
            text-transform: uppercase;
        }

        .summary-value {
            font-size: 12pt;
            font-weight: 700;
            color: var(--primary-color);
        }

        /* Section styling */
        .section {
            margin-bottom: 8mm;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 12pt;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 3mm;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1mm;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 3mm;
        }

        th {
            background-color: var(--secondary-color);
            color: var(--primary-color);
            font-weight: 600;
            text-align: left;
            padding: 2mm 1mm;
            border-bottom: 1px solid var(--border-color);
            text-transform: uppercase;
            font-size: 7pt;
            letter-spacing: 0.5px;
        }

        td {
            padding: 1.5mm 1mm;
            border-bottom: 1px solid var(--border-color);
        }

        .numeric {
            text-align: right;
        }

        /* Footer styling */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7pt;
            color: #666;
            padding-top: 2mm;
            border-top: 1px solid var(--border-color);
            margin: 0 10mm;
            background-color: white;
        }
    </style>
</head>

<body>
    <div class="container">
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

        <div class="footer">
            <p>Relatório gerado em {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>

</html>
