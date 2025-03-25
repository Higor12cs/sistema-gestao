<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Pedido_{{ str_pad($order->sequential_id, 6, '0', STR_PAD_LEFT) }}</title>
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

        body {
            font-family: 'JetBrains Mono', monospace;
            font-size: 9pt;
            line-height: 1.4;
            color: var(--text-color);
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            padding: 10mm;
        }

        .container {
            width: 100%;
        }

        /* Header styling */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 5mm;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 5mm;
        }

        .order-title {
            font-size: 14pt;
            font-weight: 700;
            color: var(--primary-color);
        }

        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8pt;
            font-weight: 500;
            border-radius: 2px;
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

        /* Grid layout */
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4mm;
            margin-bottom: 5mm;
        }

        /* Section styling */
        .section {
            margin-bottom: 5mm;
        }

        .section-title {
            font-size: 10pt;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 2mm;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
            margin-bottom: 4mm;
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

        /* Totals styling */
        .totals {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 2mm;
            margin-left: auto;
            width: 50%;
            margin-bottom: 5mm;
        }

        .total-item {
            display: flex;
            justify-content: space-between;
        }

        .total-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-right: 5mm;
        }

        .total-value {
            text-align: right;
        }

        .grand-total {
            font-weight: 700;
            font-size: 10pt;
            border-top: 1px solid var(--border-color);
            padding-top: 1mm;
        }

        /* Watermark styling */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 70pt;
            opacity: 0.05;
            z-index: -1;
            color: var(--danger-color);
            font-weight: 800;
            letter-spacing: 5px;
        }

        /* Footer styling */
        .footer {
            position: fixed;
            bottom: 10mm;
            left: 10mm;
            right: 10mm;
            text-align: center;
            font-size: 7pt;
            color: #666;
            padding-top: 2mm;
            border-top: 1px solid var(--border-color);
        }

        /* Page break */
        .page-break {
            page-break-after: always;
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
            margin-top: 4mm;
            padding: 2mm;
            background-color: var(--secondary-color);
            border-radius: 2px;
        }

        /* Signature */
        .signature {
            margin-top: 30mm;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid var(--border-color);
            width: 300px;
            margin: 0 auto;
            padding-top: 2mm;
        }

        .signature-label {
            font-size: 8pt;
            color: var(--text-color);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="order-title">
                PEDIDO #{{ str_pad($order->sequential_id, 6, '0', STR_PAD_LEFT) }}
            </div>
            <div>
                <span class="status-badge {{ $order->hasReceivables() ? 'status-finalized' : 'status-pending' }}">
                    {{ $order->hasReceivables() ? 'Finalizado' : 'Pendente' }}
                </span>
            </div>
        </div>

        @if (!$order->hasReceivables())
            <div class="watermark">PENDENTE</div>
        @endif

        <div class="grid">
            <div class="section">
                <div class="section-title">Informações do Pedido</div>
                <div class="info-list">
                    <div class="info-item">
                        <div class="info-label">Código:</div>
                        <div class="info-value">#{{ str_pad($order->sequential_id, 6, '0', STR_PAD_LEFT) }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Data Emissão:</div>
                        <div class="info-value">{{ $order->issue_date->format('d/m/Y') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Criado Por:</div>
                        <div class="info-value">{{ $order->createdBy ? $order->createdBy->name : 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Data Criação:</div>
                        <div class="info-value">{{ $order->created_at->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>

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
                            {{ $order->customer ? $order->customer->cpf_cnpj : 'N/A' }}
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Telefone/WhatsApp:</div>
                        <div class="info-value">
                            {{ $order->customer ? ($order->customer->phone ? $order->customer->phone . ' | ' : '') . $order->customer->whatsapp : 'N/A' }}
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Endereço:</div>
                        <div class="info-value">
                            {{ $order->customer ? $order->customer->address . ', ' . $order->customer->number . ', ' . $order->customer->complement . ', ' . $order->customer->neighborhood . ', ' . $order->customer->city . ', ' . $order->customer->state : 'N/A' }}
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
                        <th width="8%">Item</th>
                        <th width="40%">Produto</th>
                        <th width="12%" class="numeric">Qtd</th>
                        <th width="20%" class="numeric">Preço Unit.</th>
                        <th width="20%" class="numeric">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $index => $item)
                        <tr>
                            <td>{{ str_pad($index + 1, 3, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $item->product ? $item->product->name : 'N/A' }}</td>
                            <td class="numeric">{{ $item->quantity }}</td>
                            <td class="numeric">{{ 'R$ ' . number_format($item->unit_price, 2, ',', '.') }}</td>
                            <td class="numeric">{{ 'R$ ' . number_format($item->total_price, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="totals">
                <div class="total-label">Subtotal:</div>
                <div class="total-value">R$
                    {{ number_format($order->total_price + $order->discount - $order->fees, 2, ',', '.') }}
                </div>

                <div class="total-label">Descontos (-):</div>
                <div class="total-value">R$ {{ number_format($order->discount, 2, ',', '.') }}</div>

                <div class="total-label">Taxas (+):</div>
                <div class="total-value">R$ {{ number_format($order->fees, 2, ',', '.') }}</div>

                <div class="total-label grand-total">Total:</div>
                <div class="total-value grand-total">R$ {{ number_format($order->total_price, 2, ',', '.') }}</div>
            </div>
        </div>

        @if ($order->hasReceivables())
            <div class="section">
                <div class="section-title">Recebíveis</div>
                <table>
                    <thead>
                        <tr>
                            <th width="15%">Código</th>
                            <th width="30%">Método</th>
                            <th width="15%">Vencimento</th>
                            <th width="20%" class="numeric">Valor</th>
                            <th width="20%" style="padding-left: 4rem">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->receivables as $receivable)
                            <tr>
                                <td>{{ str_pad($receivable->sequential_id, 6, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $receivable->paymentMethod ? $receivable->paymentMethod->name : 'N/A' }}</td>
                                <td>{{ $receivable->due_date->format('d/m/Y') }}</td>
                                <td class="numeric">{{ 'R$ ' . number_format($receivable->total_amount, 2, ',', '.') }}
                                </td>
                                <td style="padding-left: 4rem">
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

        <div class="signature">
            <div class="signature-line"></div>
            <div class="signature-label">
                {{ $order->customer ? $order->customer->first_name . ' ' . $order->customer->last_name : 'N/A' }}
            </div>
        </div>

        <div class="footer">
            Documento gerado em {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>
</body>

</html>