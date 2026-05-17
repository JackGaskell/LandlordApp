@extends('mail.layouts.base')

@section('content')
    <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#0f172a;">
        Hello {{ $userName }},
    </p>

    <p style="margin:0 0 16px;font-size:15px;line-height:1.65;color:#334155;">
        Thanks for signing up. Please confirm your email address to activate your account and start managing rent reminders.
    </p>

    <x-mail.button :url="$verificationUrl" label="Verify email address" />

    <p style="margin:0;font-size:13px;line-height:1.6;color:#64748b;">
        If you did not create an account, no further action is required. This link expires after 60 minutes.
    </p>

    <p style="margin:24px 0 0;font-size:12px;line-height:1.6;color:#94a3b8;word-break:break-all;">
        Button not working? Copy and paste this URL into your browser:<br>
        <a href="{{ $verificationUrl }}" style="color:{{ $brandColor }};">{{ $verificationUrl }}</a>
    </p>
@endsection
