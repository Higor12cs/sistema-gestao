<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Pedido_{{ str_pad($order->sequential_id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        /* Reset and base styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 10pt;
            width: 76mm;
            /* Standard width for 80mm thermal printers */
            margin: 0 auto;
            padding: 2mm;
            color: black;
            line-height: 1.2;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 5mm;
            border-bottom: 1px dashed black;
            padding-bottom: 2mm;
        }

        .order-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 1mm;
        }

        .status-indicator {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 2mm;
        }

        /* Section styling */
        .section {
            margin-bottom: 4mm;
        }

        .section-title {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 1mm;
            text-transform: uppercase;
            text-align: center;
            border-bottom: 1px solid black;
        }

        /* Info list */
        .info-list {
            margin-bottom: 2mm;
        }

        .info-item {
            display: flex;
            font-size: 8pt;
            margin-bottom: 0.5mm;
        }

        .info-label {
            font-weight: bold;
            width: 40%;
        }

        .info-value {
            width: 60%;
        }

        /* Divider */
        .divider {
            border-top: 1px dashed black;
            margin: 2mm 0;
        }

        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 2mm;
        }

        .items-table th {
            border-bottom: 1px solid black;
            text-align: left;
            padding: 1mm 0;
            font-weight: bold;
        }

        .items-table td {
            padding: 1mm 0;
            border-bottom: 1px dotted black;
        }

        .numeric {
            text-align: right;
        }

        /* Product row styling */
        .product-row td {
            padding-top: 2mm;
            border-bottom: none;
        }

        .details-row td {
            padding-top: 0;
            padding-left: 3mm;
            font-size: 7pt;
        }

        /* Totals */
        .totals {
            width: 100%;
            font-size: 8pt;
            margin-top: 2mm;
        }

        .total-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
        }

        .total-label {
            font-weight: bold;
        }

        .grand-total {
            font-size: 10pt;
            font-weight: bold;
            border-top: 1px solid black;
            padding-top: 1mm;
            margin-top: 1mm;
        }

        /* Signature */
        .signature {
            margin-top: 10mm;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid black;
            width: 60mm;
            margin: 0 auto;
            padding-top: 2mm;
        }

        .signature-label {
            font-size: 7pt;
            margin-top: 1mm;
        }

        /* Footer */
        .footer {
            margin-top: 5mm;
            border-top: 1px dashed black;
            padding-top: 2mm;
            text-align: center;
            font-size: 7pt;
        }

        /* Draft watermark */
        .draft-indicator {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin: 3mm 0;
            border: 1px solid black;
            padding: 1mm;
        }

        /* Item count label */
        .item-count {
            font-size: 8pt;
            text-align: center;
            margin-bottom: 2mm;
        }

        /* Receivables status */
        .status-symbol {
            font-weight: bold;
            width: 15%;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="order-title">PEDIDO #{{ str_pad($order->sequential_id, 6, '0', STR_PAD_LEFT) }}</div>
        <div class="status-indicator">
            {{ $order->hasReceivables() ? '*** FINALIZADO ***' : '*** PENDENTE ***' }}
        </div>
    </div>

    @if (!$order->hasReceivables())
        <div class="draft-indicator">
            -- RASCUNHO --
        </div>
    @endif

    <div class="section">
        <div class="section-title">INFORMAÇÕES DO PEDIDO</div>
        <div class="info-list">
            <div class="info-item">
                <div class="info-label">Código:</div>
                <div class="info-value">#{{ str_pad($order->sequential_id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Data:</div>
                <div class="info-value">{{ $order->issue_date->format('d/m/Y') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Vendedor:</div>
                <div class="info-value">{{ $order->createdBy ? $order->createdBy->name : 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">CLIENTE</div>
        <div class="info-list">
            <div class="info-item">
                <div class="info-value" style="width: 100%; text-align: center; font-weight: bold;">
                    {{ $order->customer ? $order->customer->first_name . ' ' . $order->customer->last_name : 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    <div class="divider"></div>

    <div class="section">
        <div class="section-title">ITENS DO PEDIDO</div>
        <div class="item-count">Total de itens: {{ count($order->items) }}</div>

        @foreach ($order->items as $index => $item)
            <div style="margin-bottom: 3mm;">
                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 8pt;">
                    <div>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}.
                        {{ $item->product ? $item->product->name : 'N/A' }}</div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 8pt;">
                    <div>{{ $item->quantity }} x R$ {{ number_format($item->unit_price, 2, ',', '.') }}</div>
                    <div>R$ {{ number_format($item->total_price, 2, ',', '.') }}</div>
                </div>
                <div style="border-bottom: 1px dotted black; margin-top: 1mm;"></div>
            </div>
        @endforeach

        <div class="totals">
            <div class="total-item">
                <div class="total-label">Subtotal:</div>
                <div>R$ {{ number_format($order->total_price + $order->discount - $order->fees, 2, ',', '.') }}</div>
            </div>
            <div class="total-item">
                <div class="total-label">Descontos:</div>
                <div>R$ {{ number_format($order->discount, 2, ',', '.') }}</div>
            </div>
            <div class="total-item">
                <div class="total-label">Taxas:</div>
                <div>R$ {{ number_format($order->fees, 2, ',', '.') }}</div>
            </div>
            <div class="total-item grand-total">
                <div class="total-label">TOTAL:</div>
                <div>R$ {{ number_format($order->total_price, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    @if ($order->hasReceivables())
        <div class="divider"></div>

        <div class="section">
            <div class="section-title">FORMA DE PAGAMENTO</div>

            @foreach ($order->receivables as $receivable)
                <div style="display: flex; justify-content: space-between; font-size: 8pt; margin-bottom: 1mm;">
                    <div style="width: 55%;">
                        {{ $receivable->paymentMethod ? $receivable->paymentMethod->name : 'N/A' }}
                    </div>
                    <div style="width: 30%; text-align: right;">
                        R$ {{ number_format($receivable->total_amount, 2, ',', '.') }}
                    </div>
                    <div class="status-symbol">
                        @if ($receivable->status === 'paid')
                            [✓]
                        @elseif($receivable->status === 'partial')
                            [!]
                        @else
                            [ ]
                        @endif
                    </div>
                </div>
            @endforeach

            <div style="font-size: 7pt; margin-top: 1mm; text-align: right;">
                ✓: Pago | !: Parcial | [ ]: Pendente
            </div>
        </div>
    @endif

    @if ($order->observation)
        <div class="divider"></div>

        <div class="section">
            <div class="section-title">OBSERVAÇÕES</div>
            <div style="font-size: 8pt; white-space: pre-wrap;">{{ $order->observation }}</div>
        </div>
    @endif

    <div class="signature">
        <div class="signature-line"></div>
        <div class="signature-label">
            {{ $order->customer ? $order->customer->first_name . ' ' . $order->customer->last_name : 'Assinatura do Cliente' }}
        </div>
    </div>

    <div class="footer">
        Documento gerado em {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>

</html>
