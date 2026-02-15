<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $notificacion->asunto }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>

<body
    style="margin: 0; padding: 0; background-color: #f0f4f8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; -webkit-font-smoothing: antialiased;">

    {{-- Wrapper table --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background-color: #f0f4f8;">
        <tr>
            <td align="center" style="padding: 24px 16px;">

                {{-- Container --}}
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                    style="max-width: 600px; width: 100%; border-collapse: collapse;">

                    {{-- Header --}}
                    <tr>
                        <td align="center"
                            style="background-color: #1e3a5f; padding: 28px 32px; border-radius: 12px 12px 0 0;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 12px;">
                                        <img src="{{ asset('img/logo.png') }}" alt="CODEDE San Marcos" width="64"
                                            height="64"
                                            style="display: block; border: 0; outline: none; width: 64px; height: 64px; border-radius: 8px;">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center"
                                        style="color: #ffffff; font-size: 20px; font-weight: 700; letter-spacing: -0.3px; line-height: 1.3;">
                                        CODEDE San Marcos
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center"
                                        style="color: #94b8d6; font-size: 12px; padding-top: 4px; letter-spacing: 0.5px; text-transform: uppercase;">
                                        Sistema de Gestión de Expedientes
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Blue accent line --}}
                    <tr>
                        <td style="height: 3px; background-color: #3b82f6; font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td
                            style="background-color: #ffffff; padding: 32px; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;">

                            {{-- Tipo badge --}}
                            @if ($tipoNotificacion)
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                    style="margin-bottom: 20px;">
                                    <tr>
                                        <td
                                            style="background-color: #eff6ff; color: #1e40af; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; padding: 5px 14px; border-radius: 20px; border: 1px solid #bfdbfe;">
                                            {{ $tipoNotificacion->nombre }}
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            {{-- Saludo --}}
                            <p style="font-size: 15px; color: #334155; margin: 0 0 20px 0; line-height: 1.6;">
                                Estimado/a <strong
                                    style="color: #1e293b;">{{ $notificacion->destinatario_nombre ?? 'Usuario' }}</strong>,
                            </p>

                            {{-- Mensaje --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="margin: 0 0 24px 0;">
                                <tr>
                                    <td style="width: 4px; background-color: #3b82f6; border-radius: 4px 0 0 4px;"></td>
                                    <td
                                        style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-left: none; border-radius: 0 8px 8px 0; padding: 18px 20px;">
                                        <p
                                            style="margin: 0; font-size: 14px; color: #475569; line-height: 1.7; white-space: pre-line;">
                                            {{ $notificacion->mensaje }}</p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Contexto: Expediente --}}
                            @if ($expediente)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                    style="margin: 0 0 24px 0; border: 1px solid #bfdbfe; border-radius: 8px; overflow: hidden;">
                                    {{-- Card header --}}
                                    <tr>
                                        <td colspan="2" style="background-color: #1e40af; padding: 10px 16px;">
                                            <p
                                                style="margin: 0; font-size: 11px; color: #ffffff; text-transform: uppercase; letter-spacing: 1px; font-weight: 700;">
                                                &#128203; Información del Expediente
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="background-color: #eff6ff; padding: 10px 16px; font-size: 13px; color: #64748b; border-bottom: 1px solid #dbeafe; width: 40%;">
                                            Código SNIP</td>
                                        <td
                                            style="background-color: #eff6ff; padding: 10px 16px; font-size: 13px; color: #1e293b; font-weight: 600; border-bottom: 1px solid #dbeafe;">
                                            {{ $expediente->codigo_snip }}</td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="background-color: #f8fafc; padding: 10px 16px; font-size: 13px; color: #64748b; border-bottom: 1px solid #dbeafe;">
                                            Proyecto</td>
                                        <td
                                            style="background-color: #f8fafc; padding: 10px 16px; font-size: 13px; color: #1e293b; font-weight: 600; border-bottom: 1px solid #dbeafe;">
                                            {{ $expediente->nombre_proyecto }}</td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="background-color: #eff6ff; padding: 10px 16px; font-size: 13px; color: #64748b;{{ $expediente->municipio ? ' border-bottom: 1px solid #dbeafe;' : '' }}">
                                            Estado</td>
                                        <td
                                            style="background-color: #eff6ff; padding: 10px 16px; font-size: 13px; color: #1e293b; font-weight: 600;{{ $expediente->municipio ? ' border-bottom: 1px solid #dbeafe;' : '' }}">
                                            {{ $expediente->estado }}</td>
                                    </tr>
                                    @if ($expediente->municipio)
                                        <tr>
                                            <td
                                                style="background-color: #f8fafc; padding: 10px 16px; font-size: 13px; color: #64748b;">
                                                Municipio</td>
                                            <td
                                                style="background-color: #f8fafc; padding: 10px 16px; font-size: 13px; color: #1e293b; font-weight: 600;">
                                                {{ $expediente->municipio->nombre }}</td>
                                        </tr>
                                    @endif
                                </table>

                                {{-- Botón --}}
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                    style="margin: 0 0 24px 0;">
                                    <tr>
                                        <td align="center" style="padding: 4px 0;">
                                            <!--[if mso]>
                                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ route('expedientes.show', $expediente->id) }}" style="height:44px;v-text-anchor:middle;width:260px;" arcsize="18%" fillcolor="#1e40af" stroke="f">
                                                <w:anchorlock/>
                                                <center style="color:#ffffff;font-family:sans-serif;font-size:14px;font-weight:bold;">Ver Expediente →</center>
                                            </v:roundrect>
                                            <![endif]-->
                                            <!--[if !mso]><!-->
                                            <a href="{{ route('expedientes.show', $expediente->id) }}" target="_blank"
                                                style="display: inline-block; background-color: #1e40af; color: #ffffff; text-decoration: none; padding: 12px 32px; border-radius: 8px; font-size: 14px; font-weight: 600; letter-spacing: 0.3px;">
                                                Ver Expediente &rarr;
                                            </a>
                                            <!--<![endif]-->
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            {{-- Contexto: Municipio (sin expediente) --}}
                            @if (!$expediente && $municipio)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                    style="margin: 0 0 24px 0; border: 1px solid #bfdbfe; border-radius: 8px; overflow: hidden;">
                                    <tr>
                                        <td colspan="2" style="background-color: #1e40af; padding: 10px 16px;">
                                            <p
                                                style="margin: 0; font-size: 11px; color: #ffffff; text-transform: uppercase; letter-spacing: 1px; font-weight: 700;">
                                                &#127963; Municipio
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="background-color: #eff6ff; padding: 10px 16px; font-size: 13px; color: #64748b; border-bottom: 1px solid #dbeafe; width: 40%;">
                                            Nombre</td>
                                        <td
                                            style="background-color: #eff6ff; padding: 10px 16px; font-size: 13px; color: #1e293b; font-weight: 600; border-bottom: 1px solid #dbeafe;">
                                            {{ $municipio->nombre }}</td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="background-color: #f8fafc; padding: 10px 16px; font-size: 13px; color: #64748b;">
                                            Departamento</td>
                                        <td
                                            style="background-color: #f8fafc; padding: 10px 16px; font-size: 13px; color: #1e293b; font-weight: 600;">
                                            {{ $municipio->departamento }}</td>
                                    </tr>
                                </table>
                            @endif

                            {{-- Remitente --}}
                            @if ($remitente)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                    border="0" style="border-top: 1px solid #e2e8f0; margin-top: 8px;">
                                    <tr>
                                        <td style="padding: 18px 0 0 0;">
                                            <p
                                                style="margin: 0 0 2px 0; font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
                                                Enviado por</p>
                                            <p
                                                style="margin: 0 0 2px 0; font-size: 14px; color: #1e293b; font-weight: 600;">
                                                {{ $remitente->nombre_completo }}</p>
                                            <p style="margin: 0; font-size: 13px; color: #64748b;">
                                                {{ $remitente->role->nombre ?? '' }} · {{ $remitente->email }}</p>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td
                            style="background-color: #f1f5f9; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 12px 12px; padding: 20px 32px; text-align: center;">
                            <p style="margin: 0 0 4px 0; font-size: 12px; color: #64748b; font-weight: 600;">CODEDE San
                                Marcos</p>
                            <p style="margin: 0 0 8px 0; font-size: 11px; color: #94a3b8;">Sistema de Gestión de
                                Expedientes</p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                border="0">
                                <tr>
                                    <td align="center" style="padding: 8px 0 0 0; border-top: 1px solid #cbd5e1;">
                                        <p style="margin: 0; font-size: 11px; color: #94a3b8;">
                                            Este es un correo automático · No responda a este mensaje
                                        </p>
                                        <p style="margin: 4px 0 0 0; font-size: 11px; color: #cbd5e1;">
                                            {{ now()->format('d/m/Y H:i') }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
