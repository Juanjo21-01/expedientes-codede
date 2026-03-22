<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte - Resumen General</title>
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
            width: 120px;
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

        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            table-layout: fixed;
        }

        .stat-card {
            border: 1px solid #d8e2ec;
            background-color: #fbfdff;
            border-radius: 8px;
            text-align: center;
            padding: 14px 8px 12px 8px;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            line-height: 1;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 8.5px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: bold;
        }

        .color-primary {
            color: #1d4ed8;
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

        .estados-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #d7e0ea;
            border-radius: 8px;
            overflow: hidden;
        }

        .estados-table thead {
            display: table-header-group;
        }

        .estados-table tr {
            page-break-inside: avoid;
        }

        .estados-table th {
            background-color: #163a63;
            color: #ffffff;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 9px 10px;
            border-right: 1px solid #2a4d73;
            text-align: center;
        }

        .estados-table th:first-child {
            text-align: left;
        }

        .estados-table th:last-child {
            border-right: none;
        }

        .estados-table td {
            padding: 9px 10px;
            font-size: 10px;
            border-top: 1px solid #e5edf5;
            text-align: center;
        }

        .estados-table td:first-child {
            text-align: left;
            font-weight: bold;
        }

        .estados-table tbody tr:nth-child(even) {
            background-color: #f8fbff;
        }

        .estados-table tfoot td {
            background-color: #eef5ff;
            font-weight: bold;
            border-top: 2px solid #60a5fa;
        }

        .financial-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            table-layout: fixed;
        }

        .financial-card {
            border: 1px solid #d8e2ec;
            border-radius: 8px;
            overflow: hidden;
            background-color: #ffffff;
        }

        .financial-inner {
            width: 100%;
            border-collapse: collapse;
        }

        .financial-label {
            width: 40%;
            background-color: #f1f6fc;
            color: #5b6b7c;
            font-size: 9px;
            font-weight: bold;
            padding: 10px 12px;
            border-right: 1px solid #d8e2ec;
        }

        .financial-value {
            background-color: #ffffff;
            color: #1f2937;
            font-size: 16px;
            font-weight: bold;
            padding: 10px 12px;
        }

        .footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #d7e0ea;
            text-align: center;
            font-size: 9px;
            color: #7b8794;
        }

        .footer strong {
            color: #4b5563;
        }

        .page-break {
            page-break-after: always;
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

            {{-- Meta información --}}
            <div class="block">
                <div class="report-meta">
                    <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="label">Tipo de Reporte:</td>
                            <td class="value">Resumen General</td>
                            <td class="label">Período:</td>
                            <td class="value">{{ $periodoTexto }}</td>
                        </tr>
                        <tr>
                            <td class="label">Municipio:</td>
                            <td class="value">{{ $municipioNombre }}</td>
                            <td class="label">Rol emisor:</td>
                            <td class="value">{{ $generadoPor }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="block">
                <div class="section-title">Indicadores Principales</div>
                <div class="section-line"></div>

                <table class="stats-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="stat-card">
                            <div class="stat-number color-primary">{{ $estadisticas['total'] }}</div>
                            <div class="stat-label">Total Expedientes</div>
                        </td>
                        <td class="stat-card">
                            <div class="stat-number color-warning">{{ $estadisticas['enProceso'] }}</div>
                            <div class="stat-label">En Proceso</div>
                        </td>
                        <td class="stat-card">
                            <div class="stat-number color-success">{{ $estadisticas['aprobados'] }}</div>
                            <div class="stat-label">Aprobados</div>
                        </td>
                        <td class="stat-card">
                            <div class="stat-number color-error">{{ $estadisticas['rechazados'] }}</div>
                            <div class="stat-label">Rechazados</div>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Desglose por Estado --}}
            <div class="block">
                <div class="section-title">Desglose por Estado</div>
                <div class="section-line"></div>

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
                                'Aprobados' => $estadisticas['aprobados'],
                                'Rechazados' => $estadisticas['rechazados'],
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
            </div>

            {{-- Montos --}}
            <div class="block">
                <div class="section-title">Resumen Financiero</div>
                <div class="section-line"></div>

                <table class="financial-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <div class="financial-card">
                                <table class="financial-inner" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td class="financial-label">Monto Total Contratado</td>
                                        <td class="financial-value">Q
                                            {{ number_format($estadisticas['montoContratado'], 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                        <td>
                            <div class="financial-card">
                                <table class="financial-inner" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td class="financial-label">Monto Total Aprobado</td>
                                        <td class="financial-value">Q
                                            {{ number_format($estadisticas['montoAprobado'], 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
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
