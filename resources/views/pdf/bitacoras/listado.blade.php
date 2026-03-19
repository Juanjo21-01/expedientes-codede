<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Bitácora de Actividades</title>
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
            font-size: 10px;
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

        /* Stats */
        .stats-grid {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 4px;
        }

        .stats-row {
            display: table-row;
        }

        .stat-card {
            display: table-cell;
            border: 1px solid #d8e2ec;
            background-color: #fbfdff;
            border-radius: 8px;
            padding: 12px 10px;
            text-align: center;
            width: 25%;
        }

        .stat-card .number {
            font-size: 18px;
            font-weight: bold;
            line-height: 1.15;
        }

        .stat-card .stat-label {
            font-size: 8.5px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            font-weight: bold;
            line-height: 1.25;
        }

        .color-primary {
            color: #1e40af;
        }

        .color-success {
            color: #059669;
        }

        .color-warning {
            color: #d97706;
        }

        .color-error {
            color: #dc2626;
        }

        .color-info {
            color: #0284c7;
        }

        /* Tabla principal */
        .table-wrapper {
            border: none;
            border-radius: 0;
            overflow: visible;
        }

        .bitacora-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
            border: 1px solid #d7e0ea;
            border-radius: 8px;
        }

        .bitacora-table thead {
            display: table-header-group;
        }

        .bitacora-table tr {
            page-break-inside: avoid;
        }

        .bitacora-table th {
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

        .bitacora-table th:last-child {
            border-right: none;
        }

        .bitacora-table th:first-child {
            border-top-left-radius: 8px;
        }

        .bitacora-table th:last-child {
            border-top-right-radius: 8px;
        }

        /* Espacio visual repetible cuando la tabla continua en una nueva pagina. */
        .bitacora-table thead .thead-spacer th {
            background: #ffffff !important;
            border: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            height: 12px;
            line-height: 12px;
            font-size: 0;
            color: transparent;
        }

        .bitacora-table thead tr:not(.thead-spacer) th {
            border-top: 0 !important;
        }

        .bitacora-table td {
            padding: 7px 6px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e5edf5;
            font-size: 9.2px;
            vertical-align: top;
            line-height: 1.35;
            word-wrap: break-word;
            text-align: center;
        }

        .bitacora-table td:last-child {
            border-right: none;
        }

        .bitacora-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .bitacora-table tbody tr:last-child td {
            border-bottom: 1px solid #e5edf5;
        }

        .bitacora-table tbody tr:last-child td:first-child {
            border-bottom-left-radius: 8px;
        }

        .bitacora-table tbody tr:last-child td:last-child {
            border-bottom-right-radius: 8px;
        }

        .bitacora-table .col-num {
            text-align: center;
            color: #94a3b8;
            font-size: 8px;
        }

        .bitacora-table th.col-num,
        .bitacora-table td.col-num {
            width: 4%;
        }

        .bitacora-table th.col-fecha,
        .bitacora-table td.col-fecha {
            width: 13%;
        }

        .bitacora-table th.col-usuario,
        .bitacora-table td.col-usuario {
            width: 18%;
        }

        .bitacora-table th.col-entidad,
        .bitacora-table td.col-entidad {
            width: 12%;
        }

        .bitacora-table th.col-tipo,
        .bitacora-table td.col-tipo {
            width: 11%;
        }

        .bitacora-table th.col-detalle,
        .bitacora-table td.col-detalle {
            width: 42%;
        }

        .bitacora-table th.col-num {
            text-align: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .bitacora-table td.col-num {
            text-align: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .bitacora-table .col-fecha {
            white-space: nowrap;
        }

        .bitacora-table th.col-detalle {
            text-align: left;
            padding-left: 10px;
        }

        .bitacora-table td.col-detalle {
            text-align: left !important;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            line-height: 1.2;
            white-space: nowrap;
        }

        .badge-entidad-expediente {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-entidad-usuario {
            background-color: #f3e8ff;
            color: #7c3aed;
        }

        .badge-entidad-guia {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-entidad-auditoria {
            background-color: #e0f2fe;
            color: #0369a1;
        }

        .badge-entidad-notificacion {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-tipo-creacion {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-tipo-eliminacion {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-tipo-edicion {
            background-color: #e0f2fe;
            color: #0369a1;
        }

        .badge-tipo-cambio {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-tipo-revision {
            background-color: #e0e7ff;
            color: #3730a3;
        }

        .badge-tipo-reporte {
            background-color: #ede9fe;
            color: #5b21b6;
        }

        .badge-tipo-notificacion {
            background-color: #fef3c7;
            color: #92400e;
        }

        .text-muted {
            color: #94a3b8;
            font-size: 8px;
        }

        .text-left {
            text-align: left !important;
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

            {{-- Meta información --}}
            <div class="block">
                <div class="report-meta">
                    <table>
                        <tr>
                            <td class="label">Tipo de Reporte:</td>
                            <td class="value">Bitácora de Actividades</td>
                            <td class="label">Período:</td>
                            <td class="value">{{ $periodoTexto }}</td>
                        </tr>
                        <tr>
                            <td class="label">Filtros Activos:</td>
                            <td class="value" colspan="3">{{ $filtrosActivos }}</td>
                        </tr>
                        <tr>
                            <td class="label">Total Registros:</td>
                            <td class="value">{{ number_format($totalRegistros) }}</td>
                            <td class="label">Generado por:</td>
                            <td class="value">{{ $generadoPor }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Resumen por tipo --}}
            <div class="block">
                <div class="section-title">Resumen por Tipo de Acción</div>
                <div class="section-line"></div>

                <div class="stats-grid">
                    @php
                        $porTipo = $registros->groupBy('tipo')->map->count()->sortDesc();
                        $coloresTipo = [
                            'Creación' => 'color-success',
                            'Eliminación' => 'color-error',
                            'Edición' => 'color-info',
                            'Cambio de Estado' => 'color-warning',
                            'Revisión' => 'color-primary',
                            'Reporte' => 'color-primary',
                            'Notificación' => 'color-warning',
                        ];
                    @endphp
                    @forelse ($porTipo->chunk(4) as $filaTipos)
                        @php
                            $anchoFila = round(100 / max($filaTipos->count(), 1), 2);
                        @endphp
                        <div class="stats-row">
                            @foreach ($filaTipos as $tipo => $cantidad)
                                <div class="stat-card" style="width: {{ $anchoFila }}%;">
                                    <div class="number {{ $coloresTipo[$tipo] ?? 'color-primary' }}">{{ $cantidad }}</div>
                                    <div class="stat-label">{{ $tipo }}</div>
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <div class="stats-row">
                            <div class="stat-card" style="width: 100%;">
                                <div class="number" style="color: #cbd5e1;">0</div>
                                <div class="stat-label">Sin datos</div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Resumen por entidad --}}
            <div class="block">
                <div class="section-title">Resumen por Entidad</div>
                <div class="section-line"></div>

                <div class="stats-grid">
                    @php
                        $porEntidad = $registros->groupBy('entidad')->map->count()->sortDesc();
                        $anchoEntidad = round(100 / max($porEntidad->count(), 1), 2);
                        $coloresEntidad = [
                            'Expediente' => 'color-primary',
                            'Usuario' => 'color-primary',
                            'Guía' => 'color-success',
                            'Auditoría' => 'color-info',
                            'Notificación' => 'color-warning',
                        ];
                    @endphp
                    @if ($porEntidad->count() > 0)
                        <div class="stats-row">
                            @foreach ($porEntidad as $entidad => $cantidad)
                                <div class="stat-card" style="width: {{ $anchoEntidad }}%;">
                                    <div class="number {{ $coloresEntidad[$entidad] ?? 'color-primary' }}">
                                        {{ $cantidad }}</div>
                                    <div class="stat-label">{{ $entidad }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="stats-row">
                            <div class="stat-card" style="width: 100%;">
                                <div class="number" style="color: #cbd5e1;">0</div>
                                <div class="stat-label">Sin datos</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Tabla de registros --}}
            <div class="block">
                <div class="section-title">Detalle de Registros</div>
                <div class="section-line"></div>

                <div class="table-wrapper">
                    <table class="bitacora-table">
                        <thead>
                            <tr class="thead-spacer">
                                <th class="col-num">&nbsp;</th>
                                <th class="col-fecha">&nbsp;</th>
                                <th class="col-usuario">&nbsp;</th>
                                <th class="col-entidad">&nbsp;</th>
                                <th class="col-tipo">&nbsp;</th>
                                <th class="col-detalle">&nbsp;</th>
                            </tr>
                            <tr>
                                <th class="col-num">#</th>
                                <th class="col-fecha">Fecha / Hora</th>
                                <th class="col-usuario">Usuario</th>
                                <th class="col-entidad">Entidad</th>
                                <th class="col-tipo">Tipo</th>
                                <th class="col-detalle">Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($registros as $i => $registro)
                                <tr>
                                    <td class="col-num">{{ $i + 1 }}</td>
                                    <td class="col-fecha">
                                        {{ $registro->created_at->format('d/m/Y') }}
                                        <br>
                                        <span class="text-muted">{{ $registro->created_at->format('H:i:s') }}</span>
                                    </td>
                                    <td class="col-usuario text-left">
                                        {{ $registro->user?->nombre_completo ?? 'Sistema' }}
                                        <br>
                                        <span class="text-muted">{{ $registro->user?->role?->nombre ?? '' }}</span>
                                    </td>
                                    <td class="col-entidad">
                                        @php
                                            $entidadClass = match ($registro->entidad) {
                                                'Expediente' => 'badge-entidad-expediente',
                                                'Usuario' => 'badge-entidad-usuario',
                                                'Guía' => 'badge-entidad-guia',
                                                'Auditoría' => 'badge-entidad-auditoria',
                                                'Notificación' => 'badge-entidad-notificacion',
                                                default => 'badge-entidad-expediente',
                                            };
                                        @endphp
                                        <span class="badge {{ $entidadClass }}">{{ $registro->entidad }}</span>
                                        @if ($registro->entidad_id)
                                            <br><span class="text-muted">#{{ $registro->entidad_id }}</span>
                                        @endif
                                    </td>
                                    <td class="col-tipo">
                                        @php
                                            $tipoClass = match ($registro->tipo) {
                                                'Creación' => 'badge-tipo-creacion',
                                                'Eliminación' => 'badge-tipo-eliminacion',
                                                'Edición' => 'badge-tipo-edicion',
                                                'Cambio de Estado' => 'badge-tipo-cambio',
                                                'Revisión' => 'badge-tipo-revision',
                                                'Reporte' => 'badge-tipo-reporte',
                                                'Notificación' => 'badge-tipo-notificacion',
                                                default => 'badge-tipo-edicion',
                                            };
                                        @endphp
                                        <span class="badge {{ $tipoClass }}">{{ $registro->tipo }}</span>
                                    </td>
                                    <td class="col-detalle text-left">{{ $registro->detalle }}</td>
                                </tr>
                            @empty
                                <tr class="empty-row">
                                    <td colspan="6">
                                        No se encontraron registros para los filtros seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Footer --}}
            <div class="footer">
                <strong>CODEDE San Marcos</strong> · Bitácora de Actividades<br>
                Documento generado automáticamente · {{ $fechaGeneracion }} · Uso interno.
            </div>
        </div>

    </div>
</body>

</html>
