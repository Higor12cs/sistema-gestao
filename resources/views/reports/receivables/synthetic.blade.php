@extends('layouts.report')

@section('title', 'Relatório Sintético de Recebíveis')

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
        <table>
            <tr>
                <td><strong>TOTAL DE RECEBÍVEIS</strong></td>
                <td>{{ $totalReceivables }}</td>
            </tr>
            <tr>
                <td><strong>VALOR TOTAL</strong></td>
                <td>R$ {{ number_format($totalAmount, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>VALOR MÉDIO</strong></td>
                <td>R$ {{ number_format($avgAmount, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>TOTAL RECEBIDO</strong></td>
                <td>R$ {{ number_format($totalPaid, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>TOTAL RESTANTE</strong></td>
                <td>R$ {{ number_format($totalRemaining, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>% RECEBIDO</strong></td>
                <td>{{ $totalAmount > 0 ? number_format(($totalPaid / $totalAmount) * 100, 2) : 0 }}%</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>RECEBÍVEIS POR PERÍODO ({{ $groupBy == 'day' ? 'DIA' : ($groupBy == 'week' ? 'SEMANA' : 'MÊS') }})</h3>

        @if (count($summaryData) > 0)
            <table>
                <thead>
                    <tr>
                        <th>PERÍODO</th>
                        <th class="numeric">QTDE</th>
                        <th class="numeric">VALOR TOTAL</th>
                        <th class="numeric">VALOR RECEBIDO</th>
                        <th class="numeric">RESTANTE</th>
                        <th class="numeric">VALOR MÉDIO</th>
                        <th class="numeric">% RECEBIDO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($summaryData as $data)
                        <tr>
                            <td>{{ $data['label'] }}</td>
                            <td class="numeric">{{ $data['count'] }}</td>
                            <td class="numeric">R$ {{ number_format($data['total'], 2, ',', '.') }}</td>
                            <td class="numeric">R$ {{ number_format($data['paid'], 2, ',', '.') }}</td>
                            <td class="numeric">R$ {{ number_format($data['remaining'], 2, ',', '.') }}</td>
                            <td class="numeric">R$ {{ number_format($data['avg_amount'], 2, ',', '.') }}</td>
                            <td class="numeric">
                                {{ $data['total'] > 0 ? number_format(($data['paid'] / $data['total']) * 100, 2) : 0 }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>TOTAL</th>
                        <th class="numeric">{{ $totalReceivables }}</th>
                        <th class="numeric">R$ {{ number_format($totalAmount, 2, ',', '.') }}</th>
                        <th class="numeric">R$ {{ number_format($totalPaid, 2, ',', '.') }}</th>
                        <th class="numeric">R$ {{ number_format($totalRemaining, 2, ',', '.') }}</th>
                        <th class="numeric">R$ {{ number_format($avgAmount, 2, ',', '.') }}</th>
                        <th class="numeric">
                            {{ $totalAmount > 0 ? number_format(($totalPaid / $totalAmount) * 100, 2) : 0 }}%</th>
                    </tr>
                </tfoot>
            </table>
        @else
            <p>Nenhum dado encontrado para o período selecionado.</p>
        @endif
    </div>

@endsection

@section('footer-text', 'Relatório gerado em ' . now()->format('d/m/Y H:i:s'))
