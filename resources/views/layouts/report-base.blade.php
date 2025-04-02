{{-- layouts/report-base.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/jetbrains-mono@4.5.0/index.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #ecf0f1;
            --accent-color: #3498db;
            --danger-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --text-color: #333;
            --border-color: #ddd;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        @page {
            margin: 10mm;
            counter-increment: page;
        }

        body {
            font-family: 'JetBrains Mono', monospace;
            font-size: 9pt;
            line-height: 1.4;
            color: var(--text-color);
            width: 210mm;
            margin: 0 auto;
            padding: 1mm;
            counter-reset: page 1;
        }

        .container {
            width: 100%;
        }

        .header {
            text-align: center;
            padding-bottom: 5mm;
            /* border-bottom: 1px solid var(--border-color); */
            /* margin-bottom: 5mm; */
        }

        .report-title {
            font-size: 16pt;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 2mm;
        }

        .report-subtitle {
            font-size: 10pt;
            color: var(--text-color);
            margin-bottom: 2mm;
        }

        .report-date {
            font-size: 8pt;
            color: #666;
            margin-top: 1mm;
        }

        .summary {
            background-color: var(--secondary-color);
            padding: 4mm;
            border-radius: 2mm;
            margin-bottom: 8mm;
        }

        .summary-title {
            font-size: 12pt;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 3mm;
            text-align: center;
        }

        .summary-grid {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .summary-item {
            width: 30%;
            text-align: center;
            margin-bottom: 3mm;
        }

        .summary-label {
            font-size: 8pt;
            color: var(--text-color);
            margin-bottom: 1mm;
            text-transform: uppercase;
        }

        .summary-value {
            font-size: 12pt;
            font-weight: 700;
            color: var(--primary-color);
        }

        .section {
            margin-bottom: 4mm;
        }

        .section-title {
            font-size: 9pt;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 2mm;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1mm;
        }

        .order {
            margin-bottom: 10mm;
            border: 1px solid var(--border-color);
            border-radius: 2mm;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .order-header {
            background-color: var(--secondary-color);
            padding: 2mm 4mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-id {
            font-size: 12pt;
            font-weight: 700;
            color: var(--primary-color);
        }

        .status-badge {
            display: inline-block;
            padding: 1mm 2mm;
            font-size: 7pt;
            font-weight: 500;
            border-radius: 1mm;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-finalized {
            background-color: var(--success-color);
            color: white;
        }

        .status-pending {
            background-color: var(--warning-color);
            color: white;
        }

        .order-body {
            padding: 4mm;
        }

        .grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4mm;
        }

        .grid-column {
            width: 48%;
        }

        .info-list {
            font-size: 8pt;
        }

        .info-item {
            display: flex;
            margin-bottom: 1mm;
        }

        .info-label {
            font-weight: 600;
            width: 40%;
            color: var(--primary-color);
        }

        .info-value {
            width: 60%;
        }

        /* Configurações de tabela melhoradas */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 3mm;
            page-break-inside: auto;
            /* Permitir quebra de página dentro da tabela */
        }

        /* Configurar cabeçalho para repetir em cada página */
        thead {
            display: table-header-group;
        }

        /* Configurar rodapé para aparecer apenas no final da tabela */
        tfoot {
            display: table-footer-group;
        }

        /* Configurar linhas do corpo da tabela */
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        th {
            background-color: var(--secondary-color);
            color: var(--primary-color);
            font-weight: 600;
            text-align: left;
            padding: 1.5mm 1mm;
            border-bottom: 1px solid var(--border-color);
            text-transform: uppercase;
            font-size: 7pt;
            letter-spacing: 0.5px;
        }

        td {
            padding: 1mm;
            border-bottom: 1px solid var(--border-color);
        }

        .numeric {
            text-align: right;
        }

        .totals {
            margin-left: auto;
            width: 50%;
        }

        .total-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
        }

        .total-label {
            font-weight: 600;
            color: var(--primary-color);
        }

        .total-value {
            text-align: right;
        }

        .grand-total {
            font-weight: 700;
            font-size: 10pt;
            border-top: 1px solid var(--border-color);
            padding-top: 1mm;
            margin-top: 1mm;
        }

        .status-paid {
            color: var(--success-color);
            font-weight: 600;
        }

        .status-partial {
            color: var(--warning-color);
            font-weight: 600;
        }

        .status-pending-text {
            color: var(--danger-color);
            font-weight: 600;
        }

        .observations {
            padding: 2mm;
            background-color: var(--secondary-color);
            border-radius: 2px;
            font-size: 8pt;
        }

        .page-break {
            page-break-after: always;
            height: 0;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 70pt;
            opacity: 0.05;
            z-index: -1;
            color: var(--danger-color);
            font-weight: 800;
            letter-spacing: 5px;
        }

        .signature {
            margin-top: 30mm;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid var(--border-color);
            width: 300px;
            margin: 0 auto;
            padding-top: 2mm;
        }

        .signature-label {
            font-size: 8pt;
            color: var(--text-color);
        }

        /* Adicionar numeração de página em cada página */
        @page {
            @bottom-center {
                content: "Página " counter(page) " de " counter(pages);
                font-family: 'JetBrains Mono', monospace;
                font-size: 7pt;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            {{-- <div class="report-title">@yield('title')</div>
            <div class="report-subtitle">@yield('subtitle')</div> --}}
            <div class="report-date">Relatório gerado em {{ now()->format('d/m/Y H:i:s') }}</div>
        </div>

        @yield('content')
    </div>
</body>

</html>