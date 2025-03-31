@extends('layouts.report-base')

@section('title', 'Pedido_' . str_pad($order->sequential_id, 6, '0', STR_PAD_LEFT))

@section('content')
    <div class="header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div class="order-id">
                PEDIDO #{{ str_pad($order->sequential_id, 6, '0', STR_PAD_LEFT) }}
            </div>
            <div>
                <span class="status-badge {{ $order->hasReceivables() ? 'status-finalized' : 'status-pending' }}">
                    {{ $order->hasReceivables() ? 'Finalizado' : 'Pendente' }}
                </span>
            </div>
        </div>
    </div>

    @if (!$order->hasReceivables())
        <div class="watermark">PENDENTE</div>
    @endif

    <div class="grid">
        <div class="grid-column">
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
                            <td class="numeric">{{ 'R$ ' . number_format($receivable->total_amount, 2, ',', '.') }}</td>
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
@endsection

@section('footer-text', 'Documento gerado em ' . now()->format('d/m/Y H:i:s'))
