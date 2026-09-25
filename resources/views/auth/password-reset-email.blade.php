<x-email-layout heading="Reset your password">
    <p style="margin: 0 0 16px;">Hello {{ $recipientName }},</p>
    <p style="margin: 0 0 24px;">We received a request to reset your password. Use the button below to choose a new one.</p>
    <p style="margin: 0 0 24px; text-align: center;">
        <a href="{{ $resetUrl }}" style="display: inline-block; border-radius: 6px; background-color: #17458f; padding: 13px 24px; color: #ffffff; font-size: 14px; font-weight: 700; text-decoration: none;">Reset password</a>
    </p>
    <p style="margin: 0 0 16px;">This link expires in {{ $expireMinutes }} minutes. If you did not request a password reset, you can ignore this email.</p>
    <p style="margin: 24px 0 6px; color: #64748b; font-size: 12px;">If the button does not work, copy this link into your browser:</p>
    <p style="margin: 0; word-break: break-all; color: #17458f; font-size: 12px;">{{ $resetUrl }}</p>
</x-email-layout>
