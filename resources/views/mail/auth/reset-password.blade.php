@extends('mail.layouts.base')

@section('content')
    <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#0f172a;">
        Hello {{ $userName }},
    </p>

    <p style="margin:0 0 16px;font-size:15px;line-height:1.65;color:#334155;">
        We received a request to reset the password for your account. Click the button below to choose a new password.
    </p>

    <x-mail.button :url="$resetUrl" label="Reset password" />

    <x-mail.panel label="Security notice">
        <p style="margin:0;font-size:14px;line-height:1.6;color:#334155;">
            This link expires in <strong>{{ $expireMinutes }} minutes</strong>. If you did not request a password reset, you can safely ignore this email — your password will not change.
        </p>
    </x-mail.panel>

    <p style="margin:0;font-size:12px;line-height:1.6;color:#94a3b8;word-break:break-all;">
        Button not working? Copy and paste this URL into your browser:<br>
        <a href="{{ $resetUrl }}" style="color:{{ $brandColor }};">{{ $resetUrl }}</a>
    </p>
@endsection
