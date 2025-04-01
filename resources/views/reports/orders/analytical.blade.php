{{-- reports/orders/analytical.blade.php --}}
@extends('layouts.report-base')

@section('title', 'Relatório Analítico de Pedidos')

@section('content')
    <div class="header">
        <div class="report-title">RELATÓRIO ANALÍTICO DE PEDIDOS</div>
        <div class="report-subtitle">
            @if (request('start_date') && request('end_date'))
                Período: {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} a
                {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
            @else
                Período: Todo o histórico
            @endif
        </div>
    </div>

    <div class="summary">
        <div class="summary-title">RESUMO GERAL</div>
        <div class="summary-grid">
            @php
                $totalOrders = $orders->count();
                $finalizedOrders = $orders
                    ->filter(function ($order) {
                        return $order->hasReceivables();
                    })
                    ->count();
                $pendingOrders = $orders
                    ->filter(function ($order) {
                        return !$order->hasReceivables();
                    })
                    ->count();
                $totalItems = $orders
                    ->flatMap(function ($order) {
                        return $order->items;
                    })
                    ->sum('quantity');
                $totalSales = $orders->sum('total_price');
                $avgTicket = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

                $summaryItems = [
                    ['label' => 'Total de Pedidos', 'value' => $totalOrders],
                    ['label' => 'Valor Total', 'value' => 'R$ ' . number_format($totalSales, 2, ',', '.')],
                    ['label' => 'Ticket Médio', 'value' => 'R$ ' . number_format($avgTicket, 2, ',', '.')],
                    ['label' => 'Pedidos Finalizados', 'value' => $finalizedOrders],
                    ['label' => 'Pedidos Pendentes', 'value' => $pendingOrders],
                    ['label' => 'Total de Produtos', 'value' => $totalItems],
                ];
            @endphp

            @foreach ($summaryItems as $item)
                <div class="summary-item">
                    <div class="summary-label">{{ $item['label'] }}</div>
                    <div class="summary-value">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    @foreach ($orders as $order)
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
                                @php
                                    $orderInfo = [
                                        ['label' => 'Data Emissão:', 'value' => $order->issue_date->format('d/m/Y')],
                                        [
                                            'label' => 'Criado Por:',
                                            'value' => $order->createdBy ? $order->createdBy->name : 'N/A',
                                        ],
                                        [
                                            'label' => 'Vendedor:',
                                            'value' => $order->seller ? $order->seller->name : 'N/A',
                                        ],
                                        ['label' => 'Data Criação:', 'value' => $order->created_at->format('d/m/Y')],
                                        [
                                            'label' => 'Status:',
                                            'value' => $order->hasReceivables() ? 'Finalizado' : 'Pendente',
                                        ],
                                    ];
                                @endphp

                                @foreach ($orderInfo as $info)
                                    <div class="info-item">
                                        <div class="info-label">{{ $info['label'] }}</div>
                                        <div class="info-value">{{ $info['value'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="grid-column">
                        <div class="section">
                            <div class="section-title">Informações do Cliente</div>
                            <div class="info-list">
                                @php
                                    $customerInfo = [
                                        [
                                            'label' => 'Cliente:',
                                            'value' => $order->customer
                                                ? $order->customer->first_name . ' ' . $order->customer->last_name
                                                : 'N/A',
                                        ],
                                        [
                                            'label' => 'CPF/CNPJ:',
                                            'value' =>
                                                $order->customer && $order->customer->cpf_cnpj
                                                    ? $order->customer->cpf_cnpj
                                                    : 'N/A',
                                        ],
                                        [
                                            'label' => 'Telefone:',
                                            'value' =>
                                                $order->customer && $order->customer->phone
                                                    ? $order->customer->phone
                                                    : 'N/A',
                                        ],
                                        [
                                            'label' => 'E-mail:',
                                            'value' =>
                                                $order->customer && $order->customer->email
                                                    ? $order->customer->email
                                                    : 'N/A',
                                        ],
                                    ];
                                @endphp

                                @foreach ($customerInfo as $info)
                                    <div class="info-item">
                                        <div class="info-label">{{ $info['label'] }}</div>
                                        <div class="info-value">{{ $info['value'] }}</div>
                                    </div>
                                @endforeach
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
                        @php
                            $totals = [
                                [
                                    'label' => 'Subtotal:',
                                    'value' => $order->total_price + $order->discount - $order->fees,
                                ],
                                ['label' => 'Descontos (-):', 'value' => $order->discount],
                                ['label' => 'Taxas (+):', 'value' => $order->fees],
                                ['label' => 'Total:', 'value' => $order->total_price, 'class' => 'grand-total'],
                            ];
                        @endphp

                        @foreach ($totals as $total)
                            <div class="total-item {{ $total['class'] ?? '' }}">
                                <div class="total-label">{{ $total['label'] }}</div>
                                <div class="total-value">R$ {{ number_format($total['value'], 2, ',', '.') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

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
@endsection

@section('footer-text', 'Relatório gerado em ' . now()->format('d/m/Y H:i:s'))
