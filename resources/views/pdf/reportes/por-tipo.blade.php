<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte - Por Tipo de Solicitud</title>
    <style>
        @page {
            margin: 28px 24px 24px 24px;
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
            border: 1px solid #d7e0ea;
            border-radius: 8px;
            overflow: hidden;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
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
        }

        .data-table th:last-child {
            border-right: none;
        }

        .data-table th:first-child {
            text-align: left;
            padding-left: 10px;
        }

        .data-table td {
            padding: 8px 6px;
            text-align: center;
            border-top: 1px solid #e5edf5;
            font-size: 9.5px;
            vertical-align: middle;
            line-height: 1.35;
            word-wrap: break-word;
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
            font-size: 9.5px;
        }

        .col-tipo {
            width: 20%;
        }

        .col-small {
            width: 10%;
        }

        .col-money {
            width: 16%;
        }

        .col-pct {
            width: 14%;
        }

        .text-right {
            text-align: right !important;
        }

        .total-highlight {
            font-weight: bold;
            color: #163a63;
        }

        .progress-wrap {
            white-space: nowrap;
        }

        .progress-bar {
            display: inline-block;
            width: 62px;
            height: 9px;
            background-color: #e2e8f0;
            border-radius: 5px;
            overflow: hidden;
            vertical-align: middle;
            margin-right: 5px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 5px;
        }

        .progress-success {
            background-color: #059669;
        }

        .progress-warning {
            background-color: #d97706;
        }

        .progress-error {
            background-color: #dc2626;
        }

        .note-box {
            margin-top: 8px;
            padding: 8px 10px;
            border: 1px solid #d7e0ea;
            background-color: #fafcff;
            border-radius: 6px;
            font-size: 9px;
            color: #5b6b7c;
            line-height: 1.4;
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
                            <td class="value">Por Tipo de Solicitud</td>
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

            {{-- Tabla --}}
            <div class="block">
                <div class="section-title">Desglose por Tipo de Solicitud</div>
                <div class="section-line"></div>

                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="col-tipo">Tipo de Solicitud</th>
                                <th class="col-small">Expedientes Únicos</th>
                                <th class="col-small">Revisiones</th>
                                <th class="col-small">Aprobados</th>
                                <th class="col-small">Pendientes</th>
                                <th class="col-small">Rechazados</th>
                                <th class="col-money">Monto Aprobado</th>
                                <th class="col-pct">% Aprobación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($datos as $fila)
                                <tr>
                                    <td>{{ $fila['nombre'] }}</td>
                                    <td class="total-highlight">{{ $fila['total'] }}</td>
                                    <td>{{ $fila['revisiones'] }}</td>
                                    <td>{{ $fila['aprobados'] }}</td>
                                    <td>{{ $fila['pendientes'] }}</td>
                                    <td>{{ $fila['rechazados'] }}</td>
                                    <td class="text-right">Q {{ number_format($fila['monto_aprobado'], 2) }}</td>
                                    <td>
                                        @if ($fila['total'] > 0)
                                            <div class="progress-wrap">
                                                <span class="progress-bar">
                                                    <span
                                                        class="progress-fill {{ $fila['porcentaje_aprobacion'] >= 70 ? 'progress-success' : ($fila['porcentaje_aprobacion'] >= 40 ? 'progress-warning' : 'progress-error') }}"
                                                        style="display: block; width: {{ $fila['porcentaje_aprobacion'] }}%;"></span>
                                                </span>
                                                {{ $fila['porcentaje_aprobacion'] }}%
                                            </div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>ACUMULADO POR TIPO</td>
                                <td>{{ $datos->sum('total') }}</td>
                                <td>{{ $datos->sum('revisiones') }}</td>
                                <td>{{ $datos->sum('aprobados') }}</td>
                                <td>{{ $datos->sum('pendientes') }}</td>
                                <td>{{ $datos->sum('rechazados') }}</td>
                                <td class="text-right">Q {{ number_format($datos->sum('monto_aprobado'), 2) }}</td>
                                <td>
                                    @php
                                        $totalGlobal = $datos->sum('total');
                                        $aprobadosGlobal = $datos->sum('aprobados');
                                        $pctGlobal =
                                            $totalGlobal > 0 ? round(($aprobadosGlobal / $totalGlobal) * 100, 1) : 0;
                                    @endphp
                                    {{ $pctGlobal }}%
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="note-box">
                    Nota: un expediente puede aparecer en más de un tipo de solicitud. El acumulado por tipo no
                    representa
                    el total real de expedientes únicos del período (Total real: {{ $totalExpedientesReales ?? 0 }}).
                </div>
            </div>

            <div class="block">
                <div class="section-title">Monto Contratado Real por Estado</div>
                <div class="section-line"></div>

                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 34%;">Estado</th>
                                <th style="width: 20%;">Expedientes</th>
                                <th style="width: 46%;">Monto Contratado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($montoContratadoPorEstado['porEstado'] ?? collect() as $fila)
                                <tr>
                                    <td>{{ $fila['estado'] }}</td>
                                    <td>{{ $fila['expedientes'] }}</td>
                                    <td class="text-right">Q {{ number_format($fila['monto_contratado'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>TOTAL REAL</td>
                                <td>{{ $montoContratadoPorEstado['totalExpedientes'] ?? 0 }}</td>
                                <td class="text-right">Q
                                    {{ number_format($montoContratadoPorEstado['totalMonto'] ?? 0, 2) }}</td>
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
