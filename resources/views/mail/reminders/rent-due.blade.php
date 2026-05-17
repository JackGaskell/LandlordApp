@extends('mail.layouts.base')

@section('content')
    <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#0f172a;">
        Hello {{ $tenantName }},
    </p>

    @if ($isBeforeDue)
        <p style="margin:0 0 16px;font-size:15px;line-height:1.65;color:#334155;">
            This is a friendly reminder that your rent payment is coming up. Please ensure funds are available by the due date below.
        </p>
    @else
        <p style="margin:0 0 16px;font-size:15px;line-height:1.65;color:#334155;">
            Our records show your rent payment is still outstanding. If you have already paid, you can disregard this message or reply to your landlord.
        </p>
    @endif

    <x-mail.panel :label="$isBeforeDue ? 'Upcoming payment' : 'Outstanding payment'">
        <p style="margin:0 0 8px;font-size:28px;font-weight:700;color:#0f172a;letter-spacing:-0.02em;">
            {{ $currencySymbol }}{{ $amount }}
        </p>
        <p style="margin:0;font-size:14px;color:#64748b;">
            Due date: <strong style="color:#0f172a;">{{ $dueDate }}</strong>
        </p>
        @if ($isBeforeDue && $daysOffset > 0)
            <p style="margin:12px 0 0;font-size:13px;color:#64748b;">
                {{ $daysOffset === 1 ? 'Due tomorrow' : "Due in {$daysOffset} days" }}
            </p>
        @elseif (! $isBeforeDue && $daysOffset > 0)
            <p style="margin:12px 0 0;font-size:13px;color:#dc2626;font-weight:600;">
                {{ $daysOffset === 1 ? '1 day overdue' : "{$daysOffset} days overdue" }}
            </p>
        @endif
    </x-mail.panel>

    <p style="margin:0;font-size:14px;line-height:1.65;color:#64748b;">
        If you have already paid, please disregard this email or contact <strong style="color:#334155;">{{ $landlordName }}</strong> directly.
    </p>

    <p style="margin:24px 0 0;font-size:14px;line-height:1.65;color:#64748b;">
        Thank you,<br>
        <strong style="color:#334155;">{{ $landlordName }}</strong>
        <span style="color:#94a3b8;">via {{ $appName }}</span>
    </p>
@endsection
