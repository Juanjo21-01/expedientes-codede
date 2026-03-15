<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte - Por Municipio</title>
    <style>
        @page {
            margin: 38px 24px 26px 24px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.45;
            background: #ffffff;
        }

        .page-wrapper {
            width: 100%;
        }

        .header {
            background-color: #163a63;
            color: #ffffff;
            padding: 16px 20px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .header-logo {
            width: 46px;
            height: 46px;
            border-radius: 6px;
        }

        .header-title {
            font-size: 22px;
            font-weight: bold;
            line-height: 1.1;
            margin-bottom: 3px;
        }

        .header-subtitle {
            font-size: 9px;
            color: #c7d8ea;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .header-right-label {
            font-size: 9px;
            color: #c7d8ea;
            text-align: right;
            margin-bottom: 3px;
        }

        .header-right-value {
            font-size: 12px;
            font-weight: bold;
            color: #ffffff;
            text-align: right;
        }

        .accent-line {
            height: 4px;
            background-color: #3b82f6;
            border-radius: 0 0 10px 10px;
            margin-bottom: 16px;
        }

        .content {
            padding: 18px 20px 14px;
        }

        .block {
            margin-bottom: 16px;
        }

        .report-meta {
            border: 1px solid #cfe0f2;
            background-color: #f5f9ff;
            border-radius: 8px;
            padding: 10px 12px;
        }

        .report-meta table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-meta td {
            padding: 5px 6px;
            vertical-align: top;
        }

        .report-meta .label {
            width: 125px;
            color: #5b6b7c;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .report-meta .value {
            color: #1f2937;
            font-size: 10px;
            font-weight: bold;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #163a63;
            margin-bottom: 8px;
        }

        .section-line {
            height: 2px;
            background-color: #60a5fa;
            margin-bottom: 12px;
        }

        .table-wrapper {
            border: none;
            border-radius: 0;
            overflow: visible;
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
            border: 1px solid #d7e0ea;
            border-radius: 8px;
        }

        .data-table thead {
            display: table-header-group;
        }

        .data-table tr {
            page-break-inside: avoid;
        }

        .data-table th {
            background-color: #163a63;
            color: #ffffff;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 6px;
            text-align: center;
            border-right: 1px solid #2a4d73;
            line-height: 1.25;
            border-top: 1px solid #163a63;
        }

        .data-table th:last-child {
            border-right: none;
        }

        .data-table th:first-child {
            text-align: left;
            padding-left: 10px;
            border-top-left-radius: 8px;
        }

        .data-table th:last-child {
            border-top-right-radius: 8px;
        }

        /* Espacio visual repetible cuando la tabla continua en una nueva pagina. */
        .data-table thead .thead-spacer th {
            background: #ffffff !important;
            border: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            height: 12px;
            line-height: 12px;
            font-size: 0;
            color: transparent;
        }

        .data-table thead tr:not(.thead-spacer) th {
            border-top: 0 !important;
        }

        .data-table td {
            padding: 7px 6px;
            text-align: center;
            border-top: 1px solid #e5edf5;
            border-right: 1px solid #e5edf5;
            font-size: 9.5px;
            vertical-align: middle;
            line-height: 1.35;
            word-wrap: break-word;
        }

        .data-table td:last-child {
            border-right: none;
        }

        .data-table td:first-child {
            text-align: left;
            font-weight: bold;
            padding-left: 10px;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8fbff;
        }

        .data-table tfoot td {
            background-color: #eef5ff;
            font-weight: bold;
            border-top: 2px solid #60a5fa;
            border-right: 1px solid #e5edf5;
            font-size: 9.5px;
        }

        .data-table tfoot td:last-child {
            border-right: none;
        }

        .data-table tfoot tr:last-child td:first-child {
            border-bottom-left-radius: 8px;
        }

        .data-table tfoot tr:last-child td:last-child {
            border-bottom-right-radius: 8px;
        }

        .data-table tbody tr:last-child td:first-child {
            border-bottom-left-radius: 8px;
        }

        .data-table tbody tr:last-child td:last-child {
            border-bottom-right-radius: 8px;
        }

        .col-municipio {
            width: 20%;
        }

        .col-small {
            width: 9%;
        }

        .col-total {
            width: 8%;
        }

        .col-money {
            width: 18%;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .total-highlight {
            font-weight: bold;
            color: #163a63;
        }

        .footer {
            margin-top: 22px;
            padding-top: 10px;
            border-top: 1px solid #d7e0ea;
            text-align: center;
            font-size: 9px;
            color: #7b8794;
        }

        .footer strong {
            color: #4b5563;
        }
    </style>
</head>

<body>
    <div class="page-wrapper">

        {{-- Header --}}
        <div class="header">
            <table class="header-table" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="56">
                        <img src="{{ public_path('img/logo.png') }}" class="header-logo" alt="CODEDE">
                    </td>
                    <td>
                        <div class="header-title">CODEDE San Marcos</div>
                        <div class="header-subtitle">Sistema de Gestión de Expedientes</div>
                    </td>
                    <td width="170">
                        <div class="header-right-label">Reporte generado</div>
                        <div class="header-right-value">{{ $fechaGeneracion }}</div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="accent-line"></div>

        <div class="content">

            {{-- Meta --}}
            <div class="block">
                <div class="report-meta">
                    <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="label">Tipo de Reporte:</td>
                            <td class="value">Comparativo por Municipio</td>
                            <td class="label">Período:</td>
                            <td class="value">{{ $periodoTexto }}</td>
                        </tr>
                        <tr>
                            <td class="label">Filtro Municipio:</td>
                            <td class="value">{{ $municipioNombre }}</td>
                            <td class="label">Rol emisor:</td>
                            <td class="value">{{ $generadoPor }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Tabla principal --}}
            <div class="block">
                <div class="section-title">Expedientes por Municipio</div>
                <div class="section-line"></div>

                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr class="thead-spacer">
                                <th colspan="8">&nbsp;</th>
                            </tr>
                            <tr>
                                <th class="col-municipio">Municipio</th>
                                <th class="col-small">Recibidos</th>
                                <th class="col-small">En Revisión</th>
                                <th class="col-small">Aprobados</th>
                                <th class="col-small">Rechazados</th>
                                <th class="col-total">Total</th>
                                <th class="col-money">Monto Contratado</th>
                                <th class="col-money">Monto Aprobado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($datos as $fila)
                                @if ($fila['total'] > 0)
                                    <tr>
                                        <td>{{ $fila['nombre'] }}</td>
                                        <td>{{ $fila['recibidos'] }}</td>
                                        <td>{{ $fila['en_revision'] }}</td>
                                        <td>{{ $fila['aprobados'] }}</td>
                                        <td>{{ $fila['rechazados'] }}</td>
                                        <td class="total-highlight">{{ $fila['total'] }}</td>
                                        <td class="text-right">Q {{ number_format($fila['monto_contratado'], 2) }}</td>
                                        <td class="text-right">Q {{ number_format($fila['monto_aprobado'], 2) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>TOTAL</td>
                                <td>{{ $datos->sum('recibidos') }}</td>
                                <td>{{ $datos->sum('en_revision') }}</td>
                                <td>{{ $datos->sum('aprobados') }}</td>
                                <td>{{ $datos->sum('rechazados') }}</td>
                                <td>{{ $datos->sum('total') }}</td>
                                <td class="text-right">Q {{ number_format($datos->sum('monto_contratado'), 2) }}</td>
                                <td class="text-right">Q {{ number_format($datos->sum('monto_aprobado'), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Footer --}}
            <div class="footer">
                <strong>CODEDE San Marcos</strong> · Sistema de Gestión de Expedientes<br>
                Este documento fue generado automáticamente · {{ $fechaGeneracion }}
            </div>

        </div>
    </div>
</body>

</html>
