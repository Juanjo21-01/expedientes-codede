<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Bitácora de Actividades</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5px;
            color: #1e293b;
            line-height: 1.4;
            background: #fff;
        }

        .header {
            background-color: #1e3a5f;
            color: #ffffff;
            padding: 20px 30px;
            margin: -10px -10px 0;
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

        /* Stats */
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
            width: 25%;
        }

        .stat-card .number {
            font-size: 18px;
            font-weight: 800;
            line-height: 1.2;
        }

        .stat-card .stat-label {
            font-size: 8.5px;
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

        .color-warning {
            color: #d97706;
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

        /* Tabla principal */
        .bitacora-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
        }

        .bitacora-table thead {
            display: table-header-group;
        }

        .bitacora-table tr {
            page-break-inside: avoid;
        }

        .bitacora-table th {
            background-color: #1e3a5f;
            color: #ffffff;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 7px 8px;
            text-align: left;
        }

        .bitacora-table th:first-child {
            border-radius: 6px 0 0 0;
        }

        .bitacora-table th:last-child {
            border-radius: 0 6px 0 0;
        }

        .bitacora-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
            vertical-align: top;
        }

        .bitacora-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .bitacora-table .col-num {
            width: 30px;
            text-align: center;
            color: #94a3b8;
            font-size: 8px;
        }

        .bitacora-table .col-fecha {
            width: 80px;
            white-space: nowrap;
        }

        .bitacora-table .col-usuario {
            width: 110px;
        }

        .bitacora-table .col-entidad {
            width: 70px;
        }

        .bitacora-table .col-tipo {
            width: 80px;
        }

        .bitacora-table .col-detalle {
            /* toma el resto */
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
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

        {{-- Resumen por tipo --}}
        <div class="section-title">Resumen por Tipo de Acción</div>

        <div class="stats-grid">
            <div class="stats-row">
                @php
                    $porTipo = $registros->groupBy('tipo')->map->count()->sortDesc();
                @endphp
                @foreach ($porTipo->take(4) as $tipo => $cantidad)
                    <div class="stat-card">
                        <div class="number color-primary">{{ $cantidad }}</div>
                        <div class="stat-label">{{ $tipo }}</div>
                    </div>
                @endforeach
                @for ($i = $porTipo->take(4)->count(); $i < 4; $i++)
                    <div class="stat-card">
                        <div class="number" style="color: #cbd5e1;">0</div>
                        <div class="stat-label">—</div>
                    </div>
                @endfor
            </div>
        </div>

        {{-- Resumen por entidad --}}
        <div class="section-title">Resumen por Entidad</div>

        <div class="stats-grid">
            <div class="stats-row">
                @php
                    $porEntidad = $registros->groupBy('entidad')->map->count()->sortDesc();
                    $coloresEntidad = [
                        'Expediente' => 'color-primary',
                        'Usuario' => 'color-accent',
                        'Guía' => 'color-success',
                        'Auditoría' => 'color-info',
                        'Notificación' => 'color-warning',
                    ];
                @endphp
                @foreach ($porEntidad->take(4) as $entidad => $cantidad)
                    <div class="stat-card">
                        <div class="number {{ $coloresEntidad[$entidad] ?? 'color-primary' }}">{{ $cantidad }}</div>
                        <div class="stat-label">{{ $entidad }}</div>
                    </div>
                @endforeach
                @for ($i = $porEntidad->take(4)->count(); $i < 4; $i++)
                    <div class="stat-card">
                        <div class="number" style="color: #cbd5e1;">0</div>
                        <div class="stat-label">—</div>
                    </div>
                @endfor
            </div>
        </div>

        {{-- Tabla de registros --}}
        <div class="section-title">Detalle de Registros</div>

        <table class="bitacora-table">
            <thead>
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
                        <td class="col-usuario">
                            {{ $registro->user?->nombre_completo ?? 'Sistema' }}
                            <br>
                            <span class="text-muted">{{ $registro->user?->role?->nombre ?? '' }}</span>
                        </td>
                        <td class="col-entidad">
                            @php
                                $entidadClass = match($registro->entidad) {
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
                                $tipoClass = match($registro->tipo) {
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
                        <td class="col-detalle">{{ $registro->detalle }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px; color: #94a3b8;">
                            No se encontraron registros para los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <strong>CODEDE San Marcos</strong> – Bitácora de Actividades<br>
        Documento generado automáticamente el {{ $fechaGeneracion }}.
        Este documento es confidencial y de uso interno.
    </div>
</body>

</html>
