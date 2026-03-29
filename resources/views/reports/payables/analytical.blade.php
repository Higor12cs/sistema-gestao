@extends('layouts.report')

@section('title', 'Relatório Analítico de Pagáveis')

@section('subtitle')
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
@endsection

@section('content')

    <div class="summary">
        <h3>RESUMO GERAL</h3>
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
        @endphp
        <table>
            <tr>
                <td><strong>Total de Pagáveis</strong></td>
                <td>{{ $totalPayables }}</td>
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
                <td><strong>Pagos</strong></td>
                <td>{{ $paidPayables }}</td>
            </tr>
            <tr>
                <td><strong>Em Aberto</strong></td>
                <td>{{ $pendingPayables }}</td>
            </tr>
            <tr>
                <td><strong>Total Restante</strong></td>
                <td>R$ {{ number_format($totalRemaining, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    @foreach ($payables as $payable)
        <div class="section">
            <h3>PAGÁVEL #{{ str_pad($payable->id, 6, '0', STR_PAD_LEFT) }} -
                {{ $payable->paid_amount >= $payable->total_amount ? 'Pago' : 'Em Aberto' }}</h3>

            <h4>Informações do Pagável</h4>
            <table>
                <tr>
                    <td><strong>Emissão</strong></td>
                    <td>{{ $payable->issue_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Vencimento</strong></td>
                    <td>{{ $payable->due_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Plano de Contas</strong></td>
                    <td>{{ $payable->chartAccount ? $payable->chartAccount->code . ' - ' . $payable->chartAccount->name : 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td><strong>Método de Pagamento</strong></td>
                    <td>{{ $payable->paymentMethod ? $payable->paymentMethod->name : 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Origem</strong></td>
                    <td>{{ $payable->purchase ? 'Compra #' . str_pad($payable->purchase->id, 6, '0', STR_PAD_LEFT) : ($payable->is_manual ? 'Manual' : 'N/A') }}
                    </td>
                </tr>
            </table>

            <h4>Informações do Fornecedor</h4>
            <table>
                <tr>
                    <td><strong>Fornecedor</strong></td>
                    <td>{{ $payable->supplier ? $payable->supplier->first_name . ' ' . $payable->supplier->last_name : 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td><strong>Razão Social</strong></td>
                    <td>{{ $payable->supplier && $payable->supplier->legal_name ? $payable->supplier->legal_name : 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td><strong>CPF/CNPJ</strong></td>
                    <td>{{ $payable->supplier && $payable->supplier->cpf_cnpj ? $payable->supplier->cpf_cnpj : 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td><strong>Telefone</strong></td>
                    <td>{{ $payable->supplier && $payable->supplier->phone ? $payable->supplier->phone : 'N/A' }}</td>
                </tr>
            </table>

            <h4>Valores e Pagamentos</h4>
            <table>
                <tr>
                    <td><strong>Valor Total</strong></td>
                    <td class="numeric">R$ {{ number_format($payable->total_amount, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Valor Pago</strong></td>
                    <td class="numeric">R$ {{ number_format($payable->paid_amount, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Valor Restante</strong></td>
                    <td class="numeric">R$ {{ number_format($payable->remaining_amount, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Desconto</strong></td>
                    <td class="numeric">R$ {{ number_format($payable->discount, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Juros/Multa</strong></td>
                    <td class="numeric">R$ {{ number_format($payable->fees, 2, ',', '.') }}</td>
                </tr>
            </table>

            @if ($payable->description)
                <h4>Descrição</h4>
                <p>{{ $payable->description }}</p>
            @endif

            <hr>
        </div>
    @endforeach

@endsection

@section('footer-text', 'Relatório gerado em ' . now()->format('d/m/Y H:i:s'))
