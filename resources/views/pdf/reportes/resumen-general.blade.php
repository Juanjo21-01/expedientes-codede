<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte - Resumen General</title>
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
            line-height: 1.5;
            background: #fff;
        }

        .header {
            background-color: #1e3a5f;
            color: #ffffff;
            padding: 20px 30px;
            margin: -10px -10px 0;
        }

        .header-content {
            display: flex;
            align-items: center;
        }

        .header-logo {
            width: 50px;
            height: 50px;
            margin-right: 15px;
            border-radius: 6px;
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
            padding: 3px 8px;
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

        .stats-grid {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 16px;
        }

        .stats-row {
            display: table-row;
        }

        .stat-card {
            display: table-cell;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            text-align: center;
            width: 16.66%;
        }

        .stat-card .number {
            font-size: 20px;
            font-weight: 800;
            line-height: 1.2;
        }

        .stat-card .label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .color-primary {
            color: #1e40af;
        }

        .color-warning {
            color: #d97706;
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

        .color-accent {
            color: #7c3aed;
        }

        .estados-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
        }

        .estados-table thead {
            display: table-header-group;
        }

        .estados-table tr {
            page-break-inside: avoid;
        }

        .estados-table th {
            background-color: #1e3a5f;
            color: #ffffff;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 8px 10px;
            text-align: center;
        }

        .estados-table th:first-child {
            text-align: left;
            border-radius: 6px 0 0 0;
        }

        .estados-table th:last-child {
            border-radius: 0 6px 0 0;
        }

        .estados-table td {
            padding: 8px 10px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }

        .estados-table td:first-child {
            text-align: left;
            font-weight: 600;
        }

        .estados-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .estados-table tfoot td {
            font-weight: 700;
            background-color: #eff6ff;
            border-top: 2px solid #3b82f6;
        }

        .montos-section {
            margin-top: 16px;
        }

        .monto-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }

        .monto-label,
        .monto-value {
            display: table-cell;
            padding: 8px 12px;
        }

        .monto-label {
            background-color: #eff6ff;
            color: #64748b;
            font-size: 10px;
            font-weight: 600;
            width: 40%;
            border: 1px solid #bfdbfe;
            border-right: none;
            border-radius: 6px 0 0 6px;
        }

        .monto-value {
            background-color: #f8fafc;
            color: #1e293b;
            font-weight: 700;
            font-size: 13px;
            border: 1px solid #e2e8f0;
            border-left: none;
            border-radius: 0 6px 6px 0;
        }

        .footer {
            margin-top: 26px;
            padding-top: 12px;
            border-top: 1px solid #cbd5e1;
            text-align: center;
            color: #94a3b8;
            font-size: 9.5px;
        }

        .footer strong {
            color: #64748b;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    {{-- Header --}}
    <div class="header">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="55">
                    <img src="{{ public_path('img/logo.png') }}" class="header-logo" alt="CODEDE">
                </td>
                <td>
                    <h1>CODEDE San Marcos</h1>
                    <div class="header-subtitle">Sistema de Gestión de Expedientes</div>
                </td>
                <td style="text-align: right; vertical-align: top;">
                    <div style="font-size: 10px; color: #94b8d6;">Reporte generado</div>
                    <div style="font-size: 12px; font-weight: 600;">{{ $fechaGeneracion }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="accent-line"></div>

    <div class="content">

        {{-- Meta información --}}
        <div class="report-meta">
            <table>
                <tr>
                    <td class="label">Tipo de Reporte:</td>
                    <td class="value">Resumen General</td>
                    <td class="label">Período:</td>
                    <td class="value">{{ $periodoTexto }}</td>
                </tr>
                <tr>
                    <td class="label">Municipio:</td>
                    <td class="value">{{ $municipioNombre }}</td>
                    <td class="label">Generado por:</td>
                    <td class="value">{{ $generadoPor }}</td>
                </tr>
            </table>
        </div>

        {{-- Stats Cards --}}
        <div class="section-title">Indicadores Principales</div>

        <table width="100%" cellpadding="0" cellspacing="4" style="margin-bottom: 16px;">
            <tr>
                <td class="stat-card">
                    <div class="number color-primary">{{ $estadisticas['total'] }}</div>
                    <div class="label">Total Expedientes</div>
                </td>
                <td class="stat-card">
                    <div class="number color-warning">{{ $estadisticas['enProceso'] }}</div>
                    <div class="label">En Proceso</div>
                </td>
                <td class="stat-card">
                    <div class="number color-success">{{ $estadisticas['aprobados'] }}</div>
                    <div class="label">Aprobados</div>
                </td>
                <td class="stat-card">
                    <div class="number color-error">{{ $estadisticas['rechazados'] }}</div>
                    <div class="label">Rechazados</div>
                </td>
            </tr>
        </table>

        {{-- Desglose por Estado --}}
        <div class="section-title">Desglose por Estado</div>

        <table class="estados-table">
            <thead>
                <tr>
                    <th>Estado</th>
                    <th>Cantidad</th>
                    <th>Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $estados = [
                        'Recibidos' => $estadisticas['recibidos'],
                        'En Revisión' => $estadisticas['enRevision'],
                        'Completos' => $estadisticas['completos'],
                        'Incompletos' => $estadisticas['incompletos'],
                        'Aprobados' => $estadisticas['aprobados'],
                        'Rechazados' => $estadisticas['rechazados'],
                        'Archivados' => $estadisticas['archivados'],
                    ];
                @endphp
                @foreach ($estados as $nombre => $cantidad)
                    <tr>
                        <td>{{ $nombre }}</td>
                        <td>{{ $cantidad }}</td>
                        <td>{{ $estadisticas['total'] > 0 ? round(($cantidad / $estadisticas['total']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>TOTAL</td>
                    <td>{{ $estadisticas['total'] }}</td>
                    <td>100%</td>
                </tr>
            </tfoot>
        </table>

        {{-- Montos --}}
        <div class="section-title">Resumen Financiero</div>

        <table width="100%" cellpadding="0" cellspacing="4">
            <tr>
                <td style="width: 50%;">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="monto-label">Monto Total Contratado</td>
                            <td class="monto-value">Q {{ number_format($estadisticas['montoContratado'], 2) }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%;">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="monto-label">Monto Total Aprobado</td>
                            <td class="monto-value">Q {{ number_format($estadisticas['montoAprobado'], 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Footer --}}
        <div class="footer">
            <strong>CODEDE San Marcos</strong> · Sistema de Gestión de Expedientes<br>
            Este documento fue generado automáticamente · {{ $fechaGeneracion }}
        </div>

    </div>
</body>

</html>
