@extends('layouts.report')

@section('title', 'Relatório Analítico de Recebíveis')

@section('subtitle')
    @php
        $dateTypeLabel = 'Emissão';
        if ($date_type === 'due_date') {
            $dateTypeLabel = 'Vencimento';
        } elseif ($date_type === 'payment_date') {
            $dateTypeLabel = 'Recebimento';
        }

        $statusLabel = 'Todos';
        if ($status === 'open') {
            $statusLabel = 'Em Aberto';
        } elseif ($status === 'paid') {
            $statusLabel = 'Recebidos';
        }
    @endphp
    @if (isset($start_date) && isset($end_date))
        Período ({{ $dateTypeLabel }}): {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }} a
        {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}
    @else
        Período: Todo o histórico
    @endif
    | Status: {{ $statusLabel }}
@endsection

@section('content')

    <div class="summary">
        <h3>RESUMO GERAL</h3>
        @php
            $totalReceivables = $receivables->count();
            $pendingReceivables = $receivables
                ->filter(function ($receivable) {
                    return $receivable->paid_amount < $receivable->total_amount;
                })
                ->count();
            $paidReceivables = $receivables
                ->filter(function ($receivable) {
                    return $receivable->paid_amount >= $receivable->total_amount;
                })
                ->count();
            $totalAmount = $receivables->sum('total_amount');
            $totalPaid = $receivables->sum('paid_amount');
            $totalRemaining = $totalAmount - $totalPaid;
            $avgAmount = $totalReceivables > 0 ? $totalAmount / $totalReceivables : 0;
        @endphp
        <table>
            <tr>
                <td><strong>Total de Recebíveis</strong></td>
                <td>{{ $totalReceivables }}</td>
            </tr>
            <tr>
                <td><strong>Valor Total</strong></td>
                <td>R$ {{ number_format($totalAmount, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Valor Médio</strong></td>
                <td>R$ {{ number_format($avgAmount, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Recebidos</strong></td>
                <td>{{ $paidReceivables }}</td>
            </tr>
            <tr>
                <td><strong>Em Aberto</strong></td>
                <td>{{ $pendingReceivables }}</td>
            </tr>
            <tr>
                <td><strong>Total Restante</strong></td>
                <td>R$ {{ number_format($totalRemaining, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    @foreach ($receivables as $receivable)
        <div class="section">
            <h3>RECEBÍVEL #{{ str_pad($receivable->id, 6, '0', STR_PAD_LEFT) }} -
                {{ $receivable->paid_amount >= $receivable->total_amount ? 'Recebido' : 'Em Aberto' }}</h3>

            <h4>Informações do Recebível</h4>
            <table>
                <tr>
                    <td><strong>Emissão</strong></td>
                    <td>{{ $receivable->issue_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Vencimento</strong></td>
                    <td>{{ $receivable->due_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Plano de Contas</strong></td>
                    <td>{{ $receivable->chartAccount ? $receivable->chartAccount->code . ' - ' . $receivable->chartAccount->name : 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td><strong>Método de Pagamento</strong></td>
                    <td>{{ $receivable->paymentMethod ? $receivable->paymentMethod->name : 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Origem</strong></td>
                    <td>{{ $receivable->order ? 'Pedido #' . str_pad($receivable->order->id, 6, '0', STR_PAD_LEFT) : ($receivable->is_manual ? 'Manual' : 'N/A') }}
                    </td>
                </tr>
            </table>

            <h4>Informações do Cliente</h4>
            <table>
                <tr>
                    <td><strong>Cliente</strong></td>
                    <td>{{ $receivable->customer ? $receivable->customer->first_name . ' ' . $receivable->customer->last_name : 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td><strong>Razão Social</strong></td>
                    <td>{{ $receivable->customer && $receivable->customer->legal_name ? $receivable->customer->legal_name : 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td><strong>CPF/CNPJ</strong></td>
                    <td>{{ $receivable->customer && $receivable->customer->cpf_cnpj ? $receivable->customer->cpf_cnpj : 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td><strong>Telefone</strong></td>
                    <td>{{ $receivable->customer && $receivable->customer->phone ? $receivable->customer->phone : 'N/A' }}
                    </td>
                </tr>
            </table>

            <h4>Valores e Recebimentos</h4>
            <table>
                <tr>
                    <td><strong>Valor Total</strong></td>
                    <td class="numeric">R$ {{ number_format($receivable->total_amount, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Valor Recebido</strong></td>
                    <td class="numeric">R$ {{ number_format($receivable->paid_amount, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Valor Restante</strong></td>
                    <td class="numeric">R$ {{ number_format($receivable->remaining_amount, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Desconto</strong></td>
                    <td class="numeric">R$ {{ number_format($receivable->discount, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Juros/Multa</strong></td>
                    <td class="numeric">R$ {{ number_format($receivable->fees, 2, ',', '.') }}</td>
                </tr>
            </table>

            @if ($receivable->description)
                <h4>Descrição</h4>
                <p>{{ $receivable->description }}</p>
            @endif

            <hr>
        </div>
    @endforeach

@endsection

@section('footer-text', 'Relatório gerado em ' . now()->format('d/m/Y H:i:s'))
],
[
'label' => 'Método de Pagamento:',
'value' => $receivable->paymentMethod
? $receivable->paymentMethod->name
: 'N/A',
],
[
'label' => 'Origem:',
'value' => $receivable->order
? 'Pedido #' . str_pad($receivable->order->id, 6, '0', STR_PAD_LEFT)
: ($receivable->is_manual
? 'Manual'
: 'N/A'),
],
];
@endphp

@foreach ($receivableInfo as $info)
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
                        'value' => $receivable->customer
                            ? $receivable->customer->first_name . ' ' . $receivable->customer->last_name
                            : 'N/A',
                    ],
                    [
                        'label' => 'CPF/CNPJ:',
                        'value' =>
                            $receivable->customer && $receivable->customer->cpf_cnpj
                                ? $receivable->customer->cpf_cnpj
                                : 'N/A',
                    ],
                    [
                        'label' => 'Telefone:',
                        'value' =>
                            $receivable->customer && $receivable->customer->phone
                                ? $receivable->customer->phone
                                : 'N/A',
                    ],
                    [
                        'label' => 'Email:',
                        'value' =>
                            $receivable->customer && $receivable->customer->email
                                ? $receivable->customer->email
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
    <div class="section-title">Valores e Pagamentos</div>
    <table>
        <thead>
            <tr>
                <th width="20%">Valor Total</th>
                <th width="20%">Valor Recebido</th>
                <th width="20%">Valor Restante</th>
                <th width="20%">Desconto</th>
                <th width="20%">Juros/Multa</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>R$ {{ number_format($receivable->total_amount, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($receivable->paid_amount, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($receivable->remaining_amount, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($receivable->discount, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($receivable->fees, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</div>

@if ($receivable->description)
    <div class="section">
        <div class="section-title">Descrição</div>
        <div class="observations">
            {{ $receivable->description }}
        </div>
    </div>
@endif
</div>
</div>
@endforeach
@endsection

@section('footer-text', 'Relatório gerado em ' . now()->format('d/m/Y H:i:s'))
