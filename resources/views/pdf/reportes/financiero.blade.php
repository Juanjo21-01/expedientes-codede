<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte - Financiero</title>
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

        .summary-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            table-layout: fixed;
            margin-bottom: 4px;
        }

        .summary-card {
            border: 1px solid #d8e2ec;
            background-color: #fbfdff;
            border-radius: 8px;
            text-align: center;
            padding: 14px 10px 12px 10px;
        }

        .summary-number {
            font-size: 18px;
            font-weight: bold;
            line-height: 1.15;
            margin-bottom: 4px;
        }

        .summary-label {
            font-size: 8.5px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            font-weight: bold;
            line-height: 1.25;
        }

        .color-primary {
            color: #1d4ed8;
        }

        .color-success {
            color: #059669;
        }

        .color-error {
            color: #dc2626;
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

        /* Espacio visual que se repite al inicio de cada pagina cuando la tabla continua. */
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
            font-size: 9.2px;
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
            font-family: "Courier New", monospace;
            padding-left: 10px;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8fbff;
        }

        .data-table tbody tr:last-child td {
            border-bottom: 1px solid #e5edf5;
        }

        .data-table tbody tr:last-child td:first-child {
            border-bottom-left-radius: 8px;
        }

        .data-table tbody tr:last-child td:last-child {
            border-bottom-right-radius: 8px;
        }

        .group-row td {
            background-color: #eef4ff !important;
            color: #163a63;
            font-weight: bold !important;
            text-align: left !important;
            font-family: Helvetica, Arial, sans-serif !important;
            padding: 8px 10px !important;
            border-top: 1px solid #d7e6fb;
            border-bottom: 1px solid #d7e6fb;
        }

        .text-left {
            text-align: left !important;
        }

        .text-right {
            text-align: right !important;
        }

        .text-success {
            color: #059669;
            font-weight: bold;
        }

        .text-error {
            color: #dc2626;
            font-weight: bold;
        }

        .text-muted {
            color: #6b7280;
        }

        .estado-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 8.5px;
            font-weight: bold;
            line-height: 1.2;
            white-space: nowrap;
        }

        .badge-success {
            background-color: #d1fae5;
            color: #047857;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #b45309;
        }

        .badge-error {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .badge-info {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .badge-ghost {
            background-color: #f1f5f9;
            color: #64748b;
        }

        .col-snip {
            width: 12%;
        }

        .col-municipio {
            width: 20%;
        }

        .col-revision {
            width: 13%;
        }

        .col-money {
            width: 15%;
        }

        .col-diff {
            width: 13%;
        }

        .col-dias {
            width: 6%;
        }

        .col-estado {
            width: 11%;
        }

        .empty-row td {
            text-align: center !important;
            padding: 16px !important;
            color: #94a3b8;
            font-family: Helvetica, Arial, sans-serif !important;
            font-weight: normal !important;
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
                            <td class="value">Reporte Financiero</td>
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

            {{-- Resumen Financiero --}}
            <div class="block">
                <div class="section-title">Resumen Financiero</div>
                <div class="section-line"></div>

                <table class="summary-grid" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <div class="summary-card">
                                <div class="summary-number color-primary">Q
                                    {{ number_format($resumen['montoContratado'], 2) }}</div>
                                <div class="summary-label">Monto Total Contratado</div>
                            </div>
                        </td>
                        <td>
                            <div class="summary-card">
                                <div class="summary-number color-success">Q
                                    {{ number_format($resumen['montoAprobado'], 2) }}</div>
                                <div class="summary-label">Monto Total Aprobado</div>
                            </div>
                        </td>
                        <td>
                            <div class="summary-card">
                                <div
                                    class="summary-number {{ $resumen['diferencia'] >= 0 ? 'color-success' : 'color-error' }}">
                                    Q {{ number_format($resumen['diferencia'], 2) }}
                                </div>
                                <div class="summary-label">
                                    Diferencia
                                    ({{ $resumen['variacion'] >= 0 ? '+' : '' }}{{ $resumen['variacion'] }}%)
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Detalle --}}
            <div class="block">
                <div class="section-title">Detalle por Expediente (Agrupado por Municipio)</div>
                <div class="section-line"></div>

                @php
                    $datosAgrupados = collect($datos)->groupBy('municipio');
                @endphp

                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr class="thead-spacer">
                                <th colspan="8">&nbsp;</th>
                            </tr>
                            <tr>
                                <th class="col-snip">SNIP</th>
                                <th class="col-municipio">Municipio</th>
                                <th class="col-revision">Revisión</th>
                                <th class="col-money">Monto Contratado</th>
                                <th class="col-money">Monto Aprobado</th>
                                <th class="col-diff">Diferencia</th>
                                <th class="col-dias">Días</th>
                                <th class="col-estado">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($datosAgrupados as $municipio => $filasMunicipio)
                                <tr class="group-row">
                                    <td colspan="8">
                                        {{ $municipio }} ({{ $filasMunicipio->count() }}
                                        {{ Str::plural('expediente', $filasMunicipio->count()) }})
                                    </td>
                                </tr>

                                @foreach ($filasMunicipio as $fila)
                                    <tr>
                                        <td>{{ $fila['codigo_snip'] }}</td>
                                        <td class="text-left"
                                            style="font-family: Helvetica, Arial, sans-serif; font-weight: normal;">
                                            {{ $fila['municipio'] }}
                                        </td>
                                        <td>
                                            {{ !empty($fila['tiene_revision']) ? 'Con revisión' : 'Sin revisión' }}
                                        </td>
                                        <td class="text-right">Q {{ number_format($fila['monto_contratado'], 2) }}</td>
                                        <td class="text-right">Q {{ number_format($fila['monto_aprobado'], 2) }}</td>
                                        <td
                                            class="text-right {{ $fila['diferencia'] >= 0 ? 'text-success' : 'text-error' }}">
                                            Q {{ number_format($fila['diferencia'], 2) }}
                                        </td>
                                        <td>{{ (int) $fila['dias_tramite'] }}</td>
                                        <td>
                                            @php
                                                $badgeClass = match ($fila['estado']) {
                                                    'Aprobado' => 'badge-success',
                                                    'Rechazado' => 'badge-error',
                                                    'Recibido' => 'badge-info',
                                                    'En Revisión' => 'badge-warning',
                                                    default => 'badge-ghost',
                                                };
                                            @endphp
                                            <span
                                                class="estado-badge {{ $badgeClass }}">{{ $fila['estado'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr class="empty-row">
                                    <td colspan="8">
                                        No hay expedientes en este período
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
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
