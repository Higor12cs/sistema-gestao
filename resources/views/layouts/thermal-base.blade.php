<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 10pt;
            width: 76mm;
            margin: 0 auto;
            padding: 2mm;
            color: black;
            line-height: 1.2;
        }

        .header {
            text-align: center;
            margin-bottom: 5mm;
            border-bottom: 1px dashed black;
            padding-bottom: 2mm;
        }

        .order-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 1mm;
        }

        .status-indicator {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 2mm;
        }

        .section {
            margin-bottom: 4mm;
        }

        .section-title {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 1mm;
            text-transform: uppercase;
            text-align: center;
            border-bottom: 1px solid black;
        }

        .info-list {
            margin-bottom: 2mm;
        }

        .info-item {
            display: flex;
            font-size: 8pt;
            margin-bottom: 0.5mm;
        }

        .info-label {
            font-weight: bold;
            width: 40%;
        }

        .info-value {
            width: 60%;
        }

        .divider {
            border-top: 1px dashed black;
            margin: 2mm 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 2mm;
        }

        .items-table th {
            border-bottom: 1px solid black;
            text-align: left;
            padding: 1mm 0;
            font-weight: bold;
        }

        .items-table td {
            padding: 1mm 0;
            border-bottom: 1px dotted black;
        }

        .numeric {
            text-align: right;
        }

        .product-row td {
            padding-top: 2mm;
            border-bottom: none;
        }

        .details-row td {
            padding-top: 0;
            padding-left: 3mm;
            font-size: 7pt;
        }

        .totals {
            width: 100%;
            font-size: 8pt;
            margin-top: 2mm;
        }

        .total-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
        }

        .total-label {
            font-weight: bold;
        }

        .grand-total {
            font-size: 10pt;
            font-weight: bold;
            border-top: 1px solid black;
            padding-top: 1mm;
            margin-top: 1mm;
        }

        .signature {
            margin-top: 10mm;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid black;
            width: 60mm;
            margin: 0 auto;
            padding-top: 2mm;
        }

        .signature-label {
            font-size: 7pt;
            margin-top: 1mm;
        }

        .footer {
            margin-top: 5mm;
            border-top: 1px dashed black;
            padding-top: 2mm;
            text-align: center;
            font-size: 7pt;
        }

        .draft-indicator {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin: 3mm 0;
            border: 1px solid black;
            padding: 1mm;
        }

        .item-count {
            font-size: 8pt;
            text-align: center;
            margin-bottom: 2mm;
        }

        .status-symbol {
            font-weight: bold;
            width: 15%;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        @yield('content')
    </div>
</body>

</html>
