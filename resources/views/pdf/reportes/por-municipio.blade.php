<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte - Por Municipio</title>
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
            font-size: 8px;
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
            padding-left: 8px;
        }

        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .data-table tfoot td {
            font-weight: 700;
            background-color: #eff6ff;
            border-top: 2px solid #3b82f6;
            font-size: 10px;
        }

        .text-right {
            text-align: right !important;
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
                    <td class="value">Comparativo por Municipio</td>
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

        {{-- Tabla principal --}}
        <div class="section-title">Expedientes por Municipio</div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Municipio</th>
                    <th>Recibidos</th>
                    <th>En Revisión</th>
                    <th>Completos</th>
                    <th>Incompletos</th>
                    <th>Aprobados</th>
                    <th>Rechazados</th>
                    <th>Archivados</th>
                    <th>Total</th>
                    <th>Monto Contratado</th>
                    <th>Monto Aprobado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datos as $fila)
                    @if ($fila['total'] > 0)
                        <tr>
                            <td>{{ $fila['nombre'] }}</td>
                            <td>{{ $fila['recibidos'] }}</td>
                            <td>{{ $fila['en_revision'] }}</td>
                            <td>{{ $fila['completos'] }}</td>
                            <td>{{ $fila['incompletos'] }}</td>
                            <td>{{ $fila['aprobados'] }}</td>
                            <td>{{ $fila['rechazados'] }}</td>
                            <td>{{ $fila['archivados'] }}</td>
                            <td style="font-weight: 700;">{{ $fila['total'] }}</td>
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
                    <td>{{ $datos->sum('completos') }}</td>
                    <td>{{ $datos->sum('incompletos') }}</td>
                    <td>{{ $datos->sum('aprobados') }}</td>
                    <td>{{ $datos->sum('rechazados') }}</td>
                    <td>{{ $datos->sum('archivados') }}</td>
                    <td>{{ $datos->sum('total') }}</td>
                    <td class="text-right">Q {{ number_format($datos->sum('monto_contratado'), 2) }}</td>
                    <td class="text-right">Q {{ number_format($datos->sum('monto_aprobado'), 2) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Footer --}}
        <div class="footer">
            <strong>CODEDE San Marcos</strong> · Sistema de Gestión de Expedientes<br>
            Este documento fue generado automáticamente · {{ $fechaGeneracion }}
        </div>

    </div>
</body>

</html>
