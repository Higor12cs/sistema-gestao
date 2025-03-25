<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Relatório Analítico de Pedidos</title>
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

        /* Page counter setup */
        @page {
            margin: 10mm;
            counter-increment: page;
        }

        body {
            font-family: 'JetBrains Mono', monospace;
            font-size: 9pt;
            line-height: 1.4;
            color: var(--text-color);
            width: 210mm;
            margin: 0 auto;
            /* padding: 10mm; */
            counter-reset: page 1;
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

        /* Order section styling */
        .order {
            margin-bottom: 10mm;
            border: 1px solid var(--border-color);
            border-radius: 2mm;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .order-header {
            background-color: var(--secondary-color);
            padding: 2mm 4mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-id {
            font-size: 12pt;
            font-weight: 700;
            color: var(--primary-color);
        }

        .status-badge {
            display: inline-block;
            padding: 1mm 2mm;
            font-size: 7pt;
            font-weight: 500;
            border-radius: 1mm;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-finalized {
            background-color: var(--success-color);
            color: white;
        }

        .status-pending {
            background-color: var(--warning-color);
            color: white;
        }

        .order-body {
            padding: 4mm;
        }

        /* Grid layout */
        .grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4mm;
        }

        .grid-column {
            width: 48%;
        }

        /* Section styling */
        .section {
            margin-bottom: 4mm;
        }

        .section-title {
            font-size: 9pt;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 2mm;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1mm;
        }

        /* Info list styling */
        .info-list {
            font-size: 8pt;
        }

        .info-item {
            display: flex;
            margin-bottom: 1mm;
        }

        .info-label {
            font-weight: 600;
            width: 40%;
            color: var(--primary-color);
        }

        .info-value {
            width: 60%;
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
            padding: 1.5mm 1mm;
            border-bottom: 1px solid var(--border-color);
            text-transform: uppercase;
            font-size: 7pt;
            letter-spacing: 0.5px;
        }

        td {
            padding: 1mm;
            border-bottom: 1px solid var(--border-color);
        }

        .numeric {
            text-align: right;
        }

        /* Totals styling */
        .totals {
            margin-left: auto;
            width: 50%;
        }

        .total-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
        }

        .total-label {
            font-weight: 600;
            color: var(--primary-color);
        }

        .total-value {
            text-align: right;
        }

        .grand-total {
            font-weight: 700;
            font-size: 10pt;
            border-top: 1px solid var(--border-color);
            padding-top: 1mm;
            margin-top: 1mm;
        }

        /* Status colors for receivables */
        .status-paid {
            color: var(--success-color);
            font-weight: 600;
        }

        .status-partial {
            color: var(--warning-color);
            font-weight: 600;
        }

        .status-pending-text {
            color: var(--danger-color);
            font-weight: 600;
        }

        /* Observations */
        .observations {
            padding: 2mm;
            background-color: var(--secondary-color);
            border-radius: 2px;
            font-size: 8pt;
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

        /* Page number counter */
        .page-number::after {
            content: "Página " counter(page);
        }

        /* Page break */
        .page-break {
            page-break-after: always;
            height: 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="report-title">RELATÓRIO ANALÍTICO DE PEDIDOS</div>
            <div class="report-subtitle">
                @if(request('start_date') && request('end_date'))
                    Período: {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} a
                    {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
                @else
                    Período: Todo o histórico
                @endif
            </div>
        </div>

        <div class="summary">
            <div class="summary-title">RESUMO</div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Total de Pedidos</div>
                    <div class="summary-value">{{ $orders->count() }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Valor Total</div>
                    <div class="summary-value">R$ {{ number_format($orders->sum('total_price'), 2, ',', '.') }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Ticket Médio</div>
                    <div class="summary-value">
                        R$
                        {{ $orders->count() > 0 ? number_format($orders->sum('total_price') / $orders->count(), 2, ',', '.') : '0,00' }}
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Pedidos Finalizados</div>
                    <div class="summary-value">
                        {{ $orders->filter(function ($order) {
    return $order->hasReceivables(); })->count() }}
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Pedidos Pendentes</div>
                    <div class="summary-value">
                        {{ $orders->filter(function ($order) {
    return !$order->hasReceivables(); })->count() }}
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total de Produtos</div>
                    <div class="summary-value">
                        {{ $orders->flatMap(function ($order) {
    return $order->items; })->sum('quantity') }}
                    </div>
                </div>
            </div>
        </div>

        @foreach($orders as $order)
            <div class="order">
                <div class="order-header">
                    <div class="order-id">
                        PEDIDO #{{ str_pad($order->sequential_id, 6, '0', STR_PAD_LEFT) }}
                    </div>
                    <div>
                        <span class="status-badge {{ $order->hasReceivables() ? 'status-finalized' : 'status-pending' }}">
                            {{ $order->hasReceivables() ? 'Finalizado' : 'Pendente' }}
                        </span>
                    </div>
                </div>

                <div class="order-body">
                    <div class="grid">
                        <div class="grid-column">
                            <div class="section">
                                <div class="section-title">Informações do Pedido</div>
                                <div class="info-list">
                                    <div class="info-item">
                                        <div class="info-label">Data Emissão:</div>
                                        <div class="info-value">{{ $order->issue_date->format('d/m/Y') }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Criado Por:</div>
                                        <div class="info-value">{{ $order->createdBy ? $order->createdBy->name : 'N/A' }}
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Vendedor:</div>
                                        <div class="info-value">{{ $order->seller ? $order->seller->name : 'N/A' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Data Criação:</div>
                                        <div class="info-value">{{ $order->created_at->format('d/m/Y') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid-column">
                            <div class="section">
                                <div class="section-title">Informações do Cliente</div>
                                <div class="info-list">
                                    <div class="info-item">
                                        <div class="info-label">Cliente:</div>
                                        <div class="info-value">
                                            {{ $order->customer ? $order->customer->first_name . ' ' . $order->customer->last_name : 'N/A' }}
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">CPF/CNPJ:</div>
                                        <div class="info-value">
                                            {{ $order->customer && $order->customer->cpf_cnpj ? $order->customer->cpf_cnpj : 'N/A' }}
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Telefone:</div>
                                        <div class="info-value">
                                            {{ $order->customer && $order->customer->phone ? $order->customer->phone : 'N/A' }}
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">E-mail:</div>
                                        <div class="info-value">
                                            {{ $order->customer && $order->customer->email ? $order->customer->email : 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-title">Itens do Pedido</div>
                        <table>
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="40%">Produto</th>
                                    <th width="10%" class="numeric">Qtd</th>
                                    <th width="15%" class="numeric">Preço Unit.</th>
                                    <th width="15%" class="numeric">Desconto</th>
                                    <th width="15%" class="numeric">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->product ? $item->product->name : 'N/A' }}</td>
                                        <td class="numeric">{{ $item->quantity }}</td>
                                        <td class="numeric">{{ 'R$ ' . number_format($item->unit_price, 2, ',', '.') }}</td>
                                        <td class="numeric">{{ 'R$ ' . number_format($item->discount, 2, ',', '.') }}</td>
                                        <td class="numeric">{{ 'R$ ' . number_format($item->total_price, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="totals">
                            <div class="total-item">
                                <div class="total-label">Subtotal:</div>
                                <div class="total-value">R$
                                    {{ number_format($order->total_price + $order->discount - $order->fees, 2, ',', '.') }}
                                </div>
                            </div>

                            <div class="total-item">
                                <div class="total-label">Descontos (-):</div>
                                <div class="total-value">R$ {{ number_format($order->discount, 2, ',', '.') }}</div>
                            </div>

                            <div class="total-item">
                                <div class="total-label">Taxas (+):</div>
                                <div class="total-value">R$ {{ number_format($order->fees, 2, ',', '.') }}</div>
                            </div>

                            <div class="total-item grand-total">
                                <div class="total-label">Total:</div>
                                <div class="total-value">R$ {{ number_format($order->total_price, 2, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>

                    @if ($order->hasReceivables() && $order->receivables->count() > 0)
                        <div class="section">
                            <div class="section-title">Recebíveis</div>
                            <table>
                                <thead>
                                    <tr>
                                        <th width="20%">Código</th>
                                        <th width="20%">Método</th>
                                        <th width="15%">Vencimento</th>
                                        <th width="15%" class="numeric">Valor</th>
                                        <th width="15%" class="numeric">Pago</th>
                                        <th width="15%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->receivables as $receivable)
                                        <tr>
                                            <td>#{{ str_pad($receivable->sequential_id, 6, '0', STR_PAD_LEFT) }}</td>
                                            <td>{{ $receivable->paymentMethod ? $receivable->paymentMethod->name : 'N/A' }}</td>
                                            <td>{{ $receivable->due_date->format('d/m/Y') }}</td>
                                            <td class="numeric">{{ 'R$ ' . number_format($receivable->total_amount, 2, ',', '.') }}
                                            </td>
                                            <td class="numeric">{{ 'R$ ' . number_format($receivable->paid_amount, 2, ',', '.') }}
                                            </td>
                                            <td>
                                                @if ($receivable->status === 'paid')
                                                    <span class="status-paid">PAGO</span>
                                                @elseif($receivable->status === 'partial')
                                                    <span class="status-partial">PARCIAL</span>
                                                @else
                                                    <span class="status-pending-text">PENDENTE</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if ($order->observation)
                        <div class="section">
                            <div class="section-title">Observações</div>
                            <div class="observations">
                                {{ $order->observation }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="footer">
            <p>Relatório gerado em {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>

</html>
