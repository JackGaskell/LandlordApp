@extends('mail.layouts.base')

@section('content')
    <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#0f172a;">
        Hello {{ $landlordName }},
    </p>

    <p style="margin:0 0 16px;font-size:15px;line-height:1.65;color:#334155;">
        A rent payment has been recorded in your account.
    </p>

    <x-mail.panel label="Payment details">
        <p style="margin:0 0 8px;font-size:14px;color:#64748b;">Tenant</p>
        <p style="margin:0 0 16px;font-size:18px;font-weight:600;color:#0f172a;">{{ $tenantName }}</p>
        <p style="margin:0 0 8px;font-size:14px;color:#64748b;">Amount</p>
        <p style="margin:0 0 16px;font-size:28px;font-weight:700;color:#0f172a;letter-spacing:-0.02em;">
            {{ $currencySymbol }}{{ $amount }}
        </p>
        <p style="margin:0;font-size:14px;color:#64748b;">
            Recorded on <strong style="color:#0f172a;">{{ $paidAt }}</strong>
        </p>
    </x-mail.panel>

    <x-mail.button :url="$dashboardUrl" label="View dashboard" />

    <p style="margin:0;font-size:13px;line-height:1.6;color:#64748b;">
        Your collection health metrics will update automatically.
    </p>
@endsection
