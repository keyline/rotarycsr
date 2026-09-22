<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset your password</title>
</head>
<body style="margin: 0; background-color: #f7f4ed; color: #0b3763; font-family: Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f7f4ed; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; overflow: hidden; border: 1px solid #ead8ad; border-radius: 16px; background-color: #ffffff;">
                    <tr>
                        <td style="background-color: #0b3763; padding: 24px 32px; color: #ffffff;">
                            <p style="margin: 0; font-size: 20px; font-weight: 700;">{{ $appName }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 32px;">
                            <h1 style="margin: 0 0 20px; color: #0b3763; font-size: 26px; line-height: 1.3;">Reset your password</h1>
                            <p style="margin: 0 0 16px; color: #475569; font-size: 15px; line-height: 1.7;">Hello {{ $recipientName }},</p>
                            <p style="margin: 0 0 24px; color: #475569; font-size: 15px; line-height: 1.7;">We received a request to reset your password. Use the button below to choose a new password.</p>
                            <p style="margin: 0 0 24px; text-align: center;">
                                <a href="{{ $resetUrl }}" style="display: inline-block; border-radius: 999px; background-color: #0b3763; padding: 14px 26px; color: #ffffff; font-size: 14px; font-weight: 700; text-decoration: none;">Reset Password</a>
                            </p>
                            <p style="margin: 0 0 16px; color: #475569; font-size: 14px; line-height: 1.7;">This password reset link expires in {{ $expireMinutes }} minutes.</p>
                            <p style="margin: 0 0 16px; color: #475569; font-size: 14px; line-height: 1.7;">If you did not request a password reset, no further action is required.</p>
                            <p style="margin: 24px 0 8px; color: #64748b; font-size: 12px; line-height: 1.6;">If the button does not work, copy and paste this link into your browser:</p>
                            <p style="margin: 0; word-break: break-all; color: #17458f; font-size: 12px; line-height: 1.6;">{{ $resetUrl }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
