<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte Financiero</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10.5px;
            color: #1e293b;
            line-height: 1.45;
            background: #fff;
        }

        .header {
            background-color: #1e3a5f;
            color: #ffffff;
            padding: 20px 30px;
            margin: -10px -10px 0;
        }

        .header h1 {
            font-size: 19px;
            font-weight: 700;
            letter-spacing: -0.3px;
            margin-bottom: 2px;
        }

        .header-subtitle {
            font-size: 10px;
            color: #94b8d6;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .accent-line {
            height: 3px;
            background-color: #3b82f6;
            margin: 0 -10px;
        }

        .content {
            padding: 18px 16px 14px;
        }

        .report-meta {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 12px 16px;
            margin: 18px 0;
        }

        .report-meta table {
            width: 100%;
        }

        .report-meta td {
            padding: 2px 8px;
            font-size: 10px;
        }

        .report-meta .label {
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: 130px;
        }

        .report-meta .value {
            color: #1e293b;
            font-weight: 600;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #1e3a5f;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 6px;
            margin: 18px 0 11px;
        }

        .summary-grid {
            margin-bottom: 16px;
        }

        .summary-grid td {
            width: 33.33%;
            padding: 3px;
        }

        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            text-align: center;
        }

        .summary-card .number {
            font-size: 17px;
            font-weight: 800;
            line-height: 1.2;
        }

        .summary-card .card-label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .color-primary {
            color: #1e40af;
        }

        .color-success {
            color: #059669;
        }

        .color-error {
            color: #dc2626;
        }

        .color-info {
            color: #0284c7;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .data-table thead {
            display: table-header-group;
        }

        .data-table tr {
            page-break-inside: avoid;
        }

        .data-table th {
            background-color: #1e3a5f;
            color: #ffffff;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 6px 5px;
            text-align: center;
        }

        .data-table th:first-child {
            text-align: left;
            border-radius: 4px 0 0 0;
            padding-left: 8px;
        }

        .data-table th:last-child {
            border-radius: 0 4px 0 0;
        }

        .data-table td {
            padding: 5px 5px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
        }

        .data-table td:first-child {
            text-align: left;
            font-weight: 600;
            font-family: 'Courier New', monospace;
            padding-left: 8px;
        }

        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-right {
            text-align: right !important;
        }

        .text-success {
            color: #059669;
        }

        .text-error {
            color: #dc2626;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: 600;
        }

        .badge-success {
            background-color: #d1fae5;
            color: #059669;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #d97706;
        }

        .badge-error {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .badge-info {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-ghost {
            background-color: #f1f5f9;
            color: #64748b;
        }

        .footer {
            margin-top: 26px;
            padding-top: 10px;
            border-top: 1px solid #cbd5e1;
            text-align: center;
            color: #94a3b8;
            font-size: 9.5px;
        }

        .footer strong {
            color: #64748b;
        }
    </style>
</head>

<body>
    {{-- Header --}}
    <div class="header">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="55">
                    <img src="{{ public_path('img/logo.png') }}" style="width: 45px; height: 45px; border-radius: 6px;"
                        alt="CODEDE">
                </td>
                <td>
                    <h1>CODEDE San Marcos</h1>
                    <div class="header-subtitle">Sistema de Gestión de Expedientes</div>
                </td>
                <td style="text-align: right; vertical-align: top;">
                    <div style="font-size: 9px; color: #94b8d6;">Reporte generado</div>
                    <div style="font-size: 11px; font-weight: 600; color: #fff;">{{ $fechaGeneracion }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="accent-line"></div>

    <div class="content">

        {{-- Meta --}}
        <div class="report-meta">
            <table>
                <tr>
                    <td class="label">Tipo de Reporte:</td>
                    <td class="value">Reporte Financiero</td>
                    <td class="label">Período:</td>
                    <td class="value">{{ $periodoTexto }}</td>
                </tr>
                <tr>
                    <td class="label">Filtro Municipio:</td>
                    <td class="value">{{ $municipioNombre }}</td>
                    <td class="label">Generado por:</td>
                    <td class="value">{{ $generadoPor }}</td>
                </tr>
            </table>
        </div>

        {{-- Resumen Financiero --}}
        <div class="section-title">Resumen Financiero</div>

        <table class="summary-grid" width="100%" cellpadding="0" cellspacing="4">
            <tr>
                <td>
                    <div class="summary-card">
                        <div class="number color-primary">Q {{ number_format($resumen['montoContratado'], 2) }}</div>
                        <div class="card-label">Monto Total Contratado</div>
                    </div>
                </td>
                <td>
                    <div class="summary-card">
                        <div class="number color-success">Q {{ number_format($resumen['montoAprobado'], 2) }}</div>
                        <div class="card-label">Monto Total Aprobado</div>
                    </div>
                </td>
                <td>
                    <div class="summary-card">
                        <div class="number {{ $resumen['diferencia'] >= 0 ? 'color-success' : 'color-error' }}">
                            Q {{ number_format($resumen['diferencia'], 2) }}
                        </div>
                        <div class="card-label">Diferencia
                            ({{ $resumen['variacion'] >= 0 ? '+' : '' }}{{ $resumen['variacion'] }}%)</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="summary-card">
                        <div class="number color-info">Q {{ number_format($resumen['promedioMonto'], 2) }}</div>
                        <div class="card-label">Promedio por Expediente</div>
                    </div>
                </td>
                <td colspan="2">
                    <div class="summary-card">
                        <div class="number color-info">{{ $resumen['promedioDias'] }} días</div>
                        <div class="card-label">Promedio de trámite (recibido → aprobado)</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Detalle --}}
        <div class="section-title">Detalle por Expediente</div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>SNIP</th>
                    <th>Municipio</th>
                    <th>Tipo Solicitud</th>
                    <th>Monto Contratado</th>
                    <th>Monto Aprobado</th>
                    <th>Diferencia</th>
                    <th>Días</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($datos as $fila)
                    <tr>
                        <td>{{ $fila['codigo_snip'] }}</td>
                        <td style="text-align: left; font-family: inherit; font-weight: normal;">
                            {{ $fila['municipio'] }}
                        </td>
                        <td style="font-family: inherit; font-weight: normal; font-size: 8px;">
                            {{ $fila['tipo_solicitud'] }}</td>
                        <td class="text-right">Q {{ number_format($fila['monto_contratado'], 2) }}</td>
                        <td class="text-right">Q {{ number_format($fila['monto_aprobado'], 2) }}</td>
                        <td class="text-right {{ $fila['diferencia'] >= 0 ? 'text-success' : 'text-error' }}">
                            Q {{ number_format($fila['diferencia'], 2) }}
                        </td>
                        <td>{{ $fila['dias_tramite'] }}</td>
                        <td>
                            @php
                                $badgeClass = match ($fila['estado']) {
                                    'Aprobado' => 'badge-success',
                                    'Rechazado' => 'badge-error',
                                    'Recibido' => 'badge-info',
                                    'En Revisión' => 'badge-warning',
                                    'Completo' => 'badge-success',
                                    'Incompleto' => 'badge-error',
                                    'Archivado' => 'badge-ghost',
                                    default => 'badge-ghost',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $fila['estado'] }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 16px; color: #94a3b8;">
                            No hay expedientes con revisión financiera en este período
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Footer --}}
        <div class="footer">
            <strong>CODEDE San Marcos</strong> · Sistema de Gestión de Expedientes<br>
            Este documento fue generado automáticamente · {{ $fechaGeneracion }}
        </div>

    </div>
</body>

</html>
