@extends('layouts.report')

@section('title', 'Relatório Analítico de Pedidos')

@section('subtitle')
    @if ($start_date && $end_date)
        Período: {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }} a
        {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}
    @else
        Período: Todo o histórico
    @endif
@endsection

@section('content')

    <div class="summary">
        <h3>RESUMO GERAL</h3>
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
        @endphp
        <table>
            <tr>
                <td><strong>Total de Pedidos</strong></td>
                <td>{{ $totalOrders }}</td>
            </tr>
            <tr>
                <td><strong>Valor Total</strong></td>
                <td>R$ {{ number_format($totalSales, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Ticket Médio</strong></td>
                <td>R$ {{ number_format($avgTicket, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Pedidos Finalizados</strong></td>
                <td>{{ $finalizedOrders }}</td>
            </tr>
            <tr>
                <td><strong>Pedidos Pendentes</strong></td>
                <td>{{ $pendingOrders }}</td>
            </tr>
            <tr>
                <td><strong>Total de Produtos</strong></td>
                <td>{{ $totalItems }}</td>
            </tr>
        </table>
    </div>

    @foreach ($orders as $order)
        <div class="section">
            <h3>PEDIDO #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }} -
                {{ $order->hasReceivables() ? 'Finalizado' : 'Pendente' }}</h3>

            <h4>Informações do Pedido</h4>
            <table>
                <tr>
                    <td><strong>Data Emissão</strong></td>
                    <td>{{ $order->issue_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Criado Por</strong></td>
                    <td>{{ $order->createdBy ? $order->createdBy->name : 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Vendedor</strong></td>
                    <td>{{ $order->seller ? $order->seller->name : 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Data Criação</strong></td>
                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Status</strong></td>
                    <td>{{ $order->hasReceivables() ? 'Finalizado' : 'Pendente' }}</td>
                </tr>
            </table>

            <h4>Informações do Cliente</h4>
            <table>
                <tr>
                    <td><strong>Cliente</strong></td>
                    <td>{{ $order->customer ? $order->customer->first_name . ' ' . $order->customer->last_name : 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td><strong>CPF/CNPJ</strong></td>
                    <td>{{ $order->customer && $order->customer->cpf_cnpj ? $order->customer->cpf_cnpj : 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Telefone</strong></td>
                    <td>{{ $order->customer && $order->customer->phone ? $order->customer->phone : 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>E-mail</strong></td>
                    <td>{{ $order->customer && $order->customer->email ? $order->customer->email : 'N/A' }}</td>
                </tr>
            </table>

            <h4>Itens do Pedido</h4>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produto</th>
                        <th>Qtd</th>
                        <th>Preço Unit.</th>
                        <th>Desconto</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->product ? $item->product->name : 'N/A' }}</td>
                            <td class="numeric">{{ $item->quantity }}</td>
                            <td class="numeric">R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                            <td class="numeric">R$ {{ number_format($item->discount, 2, ',', '.') }}</td>
                            <td class="numeric">R$ {{ number_format($item->total_price, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h4>Totais</h4>
            <table>
                <tr>
                    <td><strong>Subtotal</strong></td>
                    <td class="numeric">R$
                        {{ number_format($order->total_price + $order->discount - $order->fees, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Descontos</strong></td>
                    <td class="numeric">R$ {{ number_format($order->discount, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Taxas</strong></td>
                    <td class="numeric">R$ {{ number_format($order->fees, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Total</strong></td>
                    <td class="numeric">R$ {{ number_format($order->total_price, 2, ',', '.') }}</td>
                </tr>
            </table>

            @if ($order->observation)
                <h4>Observações</h4>
                <p>{{ $order->observation }}</p>
            @endif

            <hr>
        </div>
        </div>
    @endforeach
@endsection

@section('footer-text', 'Relatório gerado em ' . now()->format('d/m/Y H:i:s'))
