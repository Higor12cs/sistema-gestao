<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }

        @page {
            margin: 15mm;
        }

        .container {
            width: 100%;
        }

        .page-header {
            margin-bottom: 20px;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .report-subtitle {
            font-size: 12px;
            margin-bottom: 5px;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .summary {
            margin-bottom: 20px;
        }

        .summary-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .summary-item {
            display: inline-block;
            width: 30%;
            margin-right: 2%;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
        }

        .summary-label {
            font-weight: bold;
            font-size: 11px;
        }

        .summary-value {
            font-size: 14px;
            margin-top: 5px;
        }

        .info-list {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .info-item {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            width: 30%;
            padding: 5px 10px 5px 0;
            border-bottom: 1px solid #ccc;
        }

        .info-value {
            display: table-cell;
            padding: 5px 10px;
            border-bottom: 1px solid #ccc;
        }

        .page-break {
            page-break-after: always;
        }

        .numeric {
            text-align: right;
        }

        h3 {
            margin-top: 15px;
            margin-bottom: 10px;
            font-size: 13px;
        }

        p {
            margin-bottom: 10px;
        }

        .grid {
            width: 100%;
            margin-bottom: 10px;
        }

        .grid-column {
            display: block;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="page-header">
            <div class="report-title">@yield('title')</div>
            <div class="report-subtitle">@yield('subtitle')</div>
        </div>

        @yield('content')
    </div>
</body>

</html>
