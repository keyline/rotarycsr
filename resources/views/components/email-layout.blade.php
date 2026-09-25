@props(['heading'])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $heading }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f6fa; color: #24354a; font-family: Arial, Helvetica, sans-serif;">
    <div style="display: none; max-height: 0; overflow: hidden; opacity: 0;">{{ $heading }}</div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f3f6fa;">
        <tr>
            <td align="center" style="padding: 32px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; border: 1px solid #dbe4ee; border-radius: 12px; background-color: #ffffff;">
                    <tr>
                        <td style="padding: 24px 32px 22px; border-bottom: 3px solid #c8942f;">
                            <img src="https://rotarycsr3291.com/images/logo.png" alt="Rotary International District 3291" width="220" style="display: block; width: 220px; max-width: 100%; height: auto; border: 0;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 32px;">
                            <p style="margin: 0 0 10px; color: #9b711e; font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;">Rotary CSR Awards 2026</p>
                            <h1 style="margin: 0 0 24px; color: #0b3763; font-size: 25px; font-weight: 700; line-height: 1.3;">{{ $heading }}</h1>
                            <div style="color: #475569; font-size: 15px; line-height: 1.7;">
                                {{ $slot }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 18px 32px; border-top: 1px solid #e7edf3; background-color: #f9fbfd; color: #64748b; font-size: 12px; line-height: 1.6;">
                            Rotary International District 3291<br>
                            CSR Awards 2026
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
