@extends('layouts.report-base')

@section('title', 'Relatório Analítico de Recebíveis')

@section('content')
    <div class="header">
        <div class="report-title">RELATÓRIO ANALÍTICO DE RECEBÍVEIS</div>
        <div class="report-subtitle">
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
        </div>
    </div>

    <div class="summary">
        <div class="summary-title">RESUMO GERAL</div>
        <div class="summary-grid">
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

                $summaryItems = [
                    ['label' => 'Total de Recebíveis', 'value' => $totalReceivables],
                    ['label' => 'Valor Total', 'value' => 'R$ ' . number_format($totalAmount, 2, ',', '.')],
                    ['label' => 'Valor Médio', 'value' => 'R$ ' . number_format($avgAmount, 2, ',', '.')],
                    ['label' => 'Recebidos', 'value' => $paidReceivables],
                    ['label' => 'Em Aberto', 'value' => $pendingReceivables],
                    ['label' => 'Total Restante', 'value' => 'R$ ' . number_format($totalRemaining, 2, ',', '.')],
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

    @foreach ($receivables as $receivable)
        <div class="order">
            <div class="order-header">
                <div class="order-id">
                    RECEBÍVEL #{{ str_pad($receivable->sequential_id, 6, '0', STR_PAD_LEFT) }}
                </div>
                <div>
                    <span
                        class="status-badge {{ $receivable->paid_amount >= $receivable->total_amount ? 'status-finalized' : 'status-pending' }}">
                        {{ $receivable->paid_amount >= $receivable->total_amount ? 'Recebido' : 'Em Aberto' }}
                    </span>
                </div>
            </div>

            <div class="order-body">
                <div class="grid">
                    <div class="grid-column">
                        <div class="section">
                            <div class="section-title">Informações do Recebível</div>
                            <div class="info-list">
                                @php
                                    $receivableInfo = [
                                        ['label' => 'Emissão:', 'value' => $receivable->issue_date->format('d/m/Y')],
                                        ['label' => 'Vencimento:', 'value' => $receivable->due_date->format('d/m/Y')],
                                        [
                                            'label' => 'Plano de Contas:',
                                            'value' => $receivable->chartAccount
                                                ? $receivable->chartAccount->code .
                                                    ' - ' .
                                                    $receivable->chartAccount->name
                                                : 'N/A',
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
                                                ? 'Pedido #' .
                                                    str_pad($receivable->order->sequential_id, 6, '0', STR_PAD_LEFT)
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
                                                ? $receivable->customer->first_name .
                                                    ' ' .
                                                    $receivable->customer->last_name
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
