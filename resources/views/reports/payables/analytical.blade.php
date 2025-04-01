@extends('layouts.report-base')

@section('title', 'Relatório Analítico de Pagáveis')

@section('content')
    <div class="header">
        <div class="report-title">RELATÓRIO ANALÍTICO DE PAGÁVEIS</div>
        <div class="report-subtitle">
            @php
                $dateTypeLabel = 'Emissão';
                if ($date_type === 'due_date') {
                    $dateTypeLabel = 'Vencimento';
                } elseif ($date_type === 'payment_date') {
                    $dateTypeLabel = 'Pagamento';
                }

                $statusLabel = 'Todos';
                if ($status === 'open') {
                    $statusLabel = 'Em Aberto';
                } elseif ($status === 'paid') {
                    $statusLabel = 'Pagos';
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
                $totalPayables = $payables->count();
                $pendingPayables = $payables
                    ->filter(function ($payable) {
                        return $payable->paid_amount < $payable->total_amount;
                    })
                    ->count();
                $paidPayables = $payables
                    ->filter(function ($payable) {
                        return $payable->paid_amount >= $payable->total_amount;
                    })
                    ->count();
                $totalAmount = $payables->sum('total_amount');
                $totalPaid = $payables->sum('paid_amount');
                $totalRemaining = $totalAmount - $totalPaid;
                $avgAmount = $totalPayables > 0 ? $totalAmount / $totalPayables : 0;

                $summaryItems = [
                    ['label' => 'Total de Pagáveis', 'value' => $totalPayables],
                    ['label' => 'Valor Total', 'value' => 'R$ ' . number_format($totalAmount, 2, ',', '.')],
                    ['label' => 'Valor Médio', 'value' => 'R$ ' . number_format($avgAmount, 2, ',', '.')],
                    ['label' => 'Pagos', 'value' => $paidPayables],
                    ['label' => 'Em Aberto', 'value' => $pendingPayables],
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

    @foreach ($payables as $payable)
        <div class="order">
            <div class="order-header">
                <div class="order-id">
                    PAGÁVEL #{{ str_pad($payable->sequential_id, 6, '0', STR_PAD_LEFT) }}
                </div>
                <div>
                    <span
                        class="status-badge {{ $payable->paid_amount >= $payable->total_amount ? 'status-finalized' : 'status-pending' }}">
                        {{ $payable->paid_amount >= $payable->total_amount ? 'Pago' : 'Em Aberto' }}
                    </span>
                </div>
            </div>

            <div class="order-body">
                <div class="grid">
                    <div class="grid-column">
                        <div class="section">
                            <div class="section-title">Informações do Pagável</div>
                            <div class="info-list">
                                @php
                                    $payableInfo = [
                                        ['label' => 'Emissão:', 'value' => $payable->issue_date->format('d/m/Y')],
                                        ['label' => 'Vencimento:', 'value' => $payable->due_date->format('d/m/Y')],
                                        [
                                            'label' => 'Plano de Contas:',
                                            'value' => $payable->chartAccount
                                                ? $payable->chartAccount->code . ' - ' . $payable->chartAccount->name
                                                : 'N/A',
                                        ],
                                        [
                                            'label' => 'Método de Pagamento:',
                                            'value' => $payable->paymentMethod ? $payable->paymentMethod->name : 'N/A',
                                        ],
                                        [
                                            'label' => 'Origem:',
                                            'value' => $payable->purchase
                                                ? 'Compra #' .
                                                    str_pad($payable->purchase->sequential_id, 6, '0', STR_PAD_LEFT)
                                                : ($payable->is_manual
                                                    ? 'Manual'
                                                    : 'N/A'),
                                        ],
                                    ];
                                @endphp

                                @foreach ($payableInfo as $info)
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
                            <div class="section-title">Informações do Fornecedor</div>
                            <div class="info-list">
                                @php
                                    $supplierInfo = [
                                        [
                                            'label' => 'Fornecedor:',
                                            'value' => $payable->supplier
                                                ? $payable->supplier->first_name . ' ' . $payable->supplier->last_name
                                                : 'N/A',
                                        ],
                                        [
                                            'label' => 'Razão Social:',
                                            'value' =>
                                                $payable->supplier && $payable->supplier->legal_name
                                                    ? $payable->supplier->legal_name
                                                    : 'N/A',
                                        ],
                                        [
                                            'label' => 'CPF/CNPJ:',
                                            'value' =>
                                                $payable->supplier && $payable->supplier->cpf_cnpj
                                                    ? $payable->supplier->cpf_cnpj
                                                    : 'N/A',
                                        ],
                                        [
                                            'label' => 'Telefone:',
                                            'value' =>
                                                $payable->supplier && $payable->supplier->phone
                                                    ? $payable->supplier->phone
                                                    : 'N/A',
                                        ],
                                    ];
                                @endphp

                                @foreach ($supplierInfo as $info)
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
                                <th width="20%">Valor Pago</th>
                                <th width="20%">Valor Restante</th>
                                <th width="20%">Desconto</th>
                                <th width="20%">Juros/Multa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>R$ {{ number_format($payable->total_amount, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($payable->paid_amount, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($payable->remaining_amount, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($payable->discount, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($payable->fees, 2, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if ($payable->description)
                    <div class="section">
                        <div class="section-title">Descrição</div>
                        <div class="observations">
                            {{ $payable->description }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endforeach
@endsection

@section('footer-text', 'Relatório gerado em ' . now()->format('d/m/Y H:i:s'))
