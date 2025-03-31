@extends('layouts.thermal-base')

@section('title', 'Pedido_' . str_pad($order->sequential_id, 6, '0', STR_PAD_LEFT))

@section('content')
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
                        {{ $item->product ? $item->product->name : 'N/A' }}
                    </div>
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
@endsection
