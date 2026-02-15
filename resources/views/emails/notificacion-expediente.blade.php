<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notificacion->asunto }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            line-height: 1.6;
        }

        .email-wrapper {
            max-width: 640px;
            margin: 0 auto;
            padding: 20px;
        }

        .email-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: 12px 12px 0 0;
            padding: 32px 30px;
            text-align: center;
        }

        .email-header h1 {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .email-header p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 13px;
            margin-top: 4px;
        }

        .email-body {
            background-color: #ffffff;
            padding: 30px;
            border-left: 1px solid #e5e7eb;
            border-right: 1px solid #e5e7eb;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-tipo {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #374151;
        }

        .message-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-left: 4px solid #3b82f6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            white-space: pre-line;
            font-size: 14px;
            color: #4b5563;
        }

        .context-card {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
        }

        .context-card h3 {
            font-size: 13px;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .context-card .detail {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 14px;
        }

        .context-card .detail .label {
            color: #6b7280;
        }

        .context-card .detail .value {
            font-weight: 600;
            color: #1f2937;
        }

        .btn-action {
            display: inline-block;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
        }

        .email-footer {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 12px 12px;
            padding: 24px 30px;
            text-align: center;
        }

        .email-footer p {
            font-size: 12px;
            color: #9ca3af;
            margin: 2px 0;
        }

        .sender-info {
            font-size: 13px;
            color: #6b7280;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .sender-info strong {
            color: #374151;
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        {{-- Header --}}
        <div class="email-header">
            <h1>CODEDE San Marcos</h1>
            <p>Sistema de Gestión de Expedientes</p>
        </div>

        {{-- Body --}}
        <div class="email-body">
            {{-- Tipo badge --}}
            @if ($tipoNotificacion)
                <div style="margin-bottom: 16px;">
                    <span class="badge badge-tipo">{{ $tipoNotificacion->nombre }}</span>
                </div>
            @endif

            {{-- Saludo --}}
            <p class="greeting">
                Estimado/a <strong>{{ $notificacion->destinatario_nombre ?? 'Usuario' }}</strong>,
            </p>

            {{-- Mensaje --}}
            <div class="message-box">{{ $notificacion->mensaje }}</div>

            {{-- Contexto: Expediente --}}
            @if ($expediente)
                <div class="context-card">
                    <h3>Información del Expediente</h3>
                    <div class="detail">
                        <span class="label">Código SNIP:</span>
                        <span class="value">{{ $expediente->codigo_snip }}</span>
                    </div>
                    <div class="detail">
                        <span class="label">Proyecto:</span>
                        <span class="value">{{ $expediente->nombre_proyecto }}</span>
                    </div>
                    <div class="detail">
                        <span class="label">Estado:</span>
                        <span class="value">{{ $expediente->estado }}</span>
                    </div>
                    @if ($expediente->municipio)
                        <div class="detail">
                            <span class="label">Municipio:</span>
                            <span class="value">{{ $expediente->municipio->nombre }}</span>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Contexto: Municipio (sin expediente) --}}
            @if (!$expediente && $municipio)
                <div class="context-card">
                    <h3>Municipio</h3>
                    <div class="detail">
                        <span class="label">Nombre:</span>
                        <span class="value">{{ $municipio->nombre }}</span>
                    </div>
                    <div class="detail">
                        <span class="label">Departamento:</span>
                        <span class="value">{{ $municipio->departamento }}</span>
                    </div>
                </div>
            @endif

            {{-- Botón de acción --}}
            @if ($expediente)
                <div style="text-align: center;">
                    <a href="{{ route('expedientes.show', $expediente->id) }}" class="btn-action">
                        Ver Expediente en el Sistema
                    </a>
                </div>
            @endif

            {{-- Remitente --}}
            @if ($remitente)
                <div class="sender-info">
                    <p>Enviado por: <strong>{{ $remitente->nombre_completo }}</strong></p>
                    <p>{{ $remitente->role->nombre ?? '' }} · {{ $remitente->email }}</p>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="email-footer">
            <p><strong>CODEDE San Marcos</strong> · Sistema de Gestión de Expedientes</p>
            <p>Este es un correo automático, por favor no responda a este mensaje.</p>
            <p style="margin-top: 8px;">{{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</body>

</html>
