@extends('layouts.report-base')

@section('title', 'Relatório Analítico de Pedidos')

@section('content')
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

    <div class="insights-section">
        <div class="insight-title">INSIGHTS DE VENDAS</div>
        <div class="insight-grid">
            <div class="insight-card">
                <div class="insight-header">Top 3 Vendedores</div>
                <div class="insight-content">
                    <table class="insight-table">
                        <thead>
                            <tr>
                                <th>Vendedor</th>
                                <th class="numeric">Pedidos</th>
                                <th class="numeric">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $topSellers = $orders->whereNotNull('seller_id')
                                    ->groupBy('seller_id')
                                    ->map(function ($group) {
                                        return [
                                            'seller' => $group->first()->seller,
                                            'count' => $group->count(),
                                            'total' => $group->sum('total_price')
                                        ];
                                    })
                                    ->sortByDesc('total')
                                    ->take(3);
                            @endphp

                            @foreach($topSellers as $seller)
                                <tr>
                                    <td>{{ $seller['seller']->name ?? 'N/A' }}</td>
                                    <td class="numeric">{{ $seller['count'] }}</td>
                                    <td class="numeric">R$ {{ number_format($seller['total'], 2, ',', '.') }}</td>
                                </tr>
                            @endforeach

                            @if($topSellers->count() == 0)
                                <tr>
                                    <td colspan="3" style="text-align: center;">Nenhum dado disponível</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="insight-card">
                <div class="insight-header">Top 3 Clientes</div>
                <div class="insight-content">
                    <table class="insight-table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th class="numeric">Pedidos</th>
                                <th class="numeric">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $topCustomers = $orders->whereNotNull('customer_id')
                                    ->groupBy('customer_id')
                                    ->map(function ($group) {
                                        return [
                                            'customer' => $group->first()->customer,
                                            'count' => $group->count(),
                                            'total' => $group->sum('total_price')
                                        ];
                                    })
                                    ->sortByDesc('total')
                                    ->take(3);
                            @endphp

                            @foreach($topCustomers as $customer)
                                <tr>
                                    <td>
                                        {{ $customer['customer'] ? $customer['customer']->first_name . ' ' . $customer['customer']->last_name : 'N/A' }}
                                    </td>
                                    <td class="numeric">{{ $customer['count'] }}</td>
                                    <td class="numeric">R$ {{ number_format($customer['total'], 2, ',', '.') }}</td>
                                </tr>
                            @endforeach

                            @if($topCustomers->count() == 0)
                                <tr>
                                    <td colspan="3" style="text-align: center;">Nenhum dado disponível</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="insight-card">
                <div class="insight-header">Produtos Mais Vendidos</div>
                <div class="insight-content">
                    <table class="insight-table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th class="numeric">Qtd</th>
                                <th class="numeric">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $topProducts = $orders->flatMap(function ($order) {
                                    return $order->items;
                                })
                                    ->groupBy('product_id')
                                    ->map(function ($group) {
                                        return [
                                            'product' => $group->first()->product,
                                            'quantity' => $group->sum('quantity'),
                                            'total' => $group->sum('total_price')
                                        ];
                                    })
                                    ->sortByDesc('quantity')
                                    ->take(3);
                            @endphp

                            @foreach($topProducts as $product)
                                <tr>
                                    <td>{{ $product['product']->name ?? 'N/A' }}</td>
                                    <td class="numeric">{{ $product['quantity'] }}</td>
                                    <td class="numeric">R$ {{ number_format($product['total'], 2, ',', '.') }}</td>
                                </tr>
                            @endforeach

                            @if($topProducts->count() == 0)
                                <tr>
                                    <td colspan="3" style="text-align: center;">Nenhum dado disponível</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="performance-metrics">
        <div class="section-title">MÉTRICAS DE DESEMPENHO</div>

        <div class="metrics-row">
            <div class="metric-card">
                <div class="metric-title">Taxa de Conversão</div>
                <div class="metric-value">
                    @php
                        $totalOrders = $orders->count();
                        $finalizedOrders = $orders->filter(function ($order) {
                            return $order->hasReceivables();
                        })->count();

                        $conversionRate = $totalOrders > 0 ?
                            round(($finalizedOrders / $totalOrders) * 100, 1) : 0;
                    @endphp
                    {{ $conversionRate }}%
                </div>
                <div class="metric-desc">Pedidos finalizados em relação ao total</div>
            </div>

            <div class="metric-card">
                <div class="metric-title">Desconto Médio</div>
                <div class="metric-value">
                    @php
                        $totalPrice = $orders->sum('total_price');
                        $totalDiscount = $orders->sum('discount');

                        $discountRate = $totalPrice > 0 ?
                            round(($totalDiscount / ($totalPrice + $totalDiscount)) * 100, 1) : 0;
                    @endphp
                    {{ $discountRate }}%
                </div>
                <div class="metric-desc">R$ {{ number_format($totalDiscount, 2, ',', '.') }} em descontos</div>
            </div>

            <div class="metric-card">
                <div class="metric-title">Itens por Pedido</div>
                <div class="metric-value">
                    @php
                        $totalItems = $orders->flatMap(function ($order) {
                            return $order->items;
                        })->sum('quantity');

                        $itemsPerOrder = $totalOrders > 0 ?
                            round($totalItems / $totalOrders, 1) : 0;
                    @endphp
                    {{ $itemsPerOrder }}
                </div>
                <div class="metric-desc">Média de itens por pedido</div>
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
                                    <div class="info-value">{{ $order->createdBy ? $order->createdBy->name : 'N/A' }}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Vendedor:</div>
                                    <div class="info-value">{{ $order->seller ? $order->seller->name : 'N/A' }}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Data Criação:</div>
                                    <div class="info-value">{{ $order->created_at->format('d/m/Y') }}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Status:</div>
                                    <div class="info-value">
                                        {{ $order->hasReceivables() ? 'Finalizado' : 'Pendente' }}
                                    </div>
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
