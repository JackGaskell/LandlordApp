@extends('mail.layouts.base')

@section('content')
    <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#0f172a;">
        Hello {{ $tenantName }},
    </p>

    @if ($isDueDay)
        <p style="margin:0 0 16px;font-size:15px;line-height:1.65;color:#334155;">
            Your rent is <strong style="color:#0f172a;">due today</strong>. Paying on time keeps your tenant score and rental record on track.
        </p>
    @elseif ($isBeforeDue)
        <p style="margin:0 0 16px;font-size:15px;line-height:1.65;color:#334155;">
            This is a friendly reminder that your rent payment is coming up. Please ensure funds are available by the due date below.
        </p>
    @else
        <p style="margin:0 0 16px;font-size:15px;line-height:1.65;color:#334155;">
            Our records show your rent payment is still outstanding. If you have already paid, confirm it in your portal so your record stays accurate.
        </p>
    @endif

    <x-mail.panel :label="$isDueDay ? 'Due today' : ($isBeforeDue ? 'Upcoming payment' : 'Outstanding payment')">
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

    @if ($showScoreBlock)
        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin:20px 0 0;border-collapse:collapse;">
            <tr>
                <td style="padding:16px 20px;background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;">
                    <p style="margin:0 0 4px;font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#64748b;">
                        Your tenant score
                    </p>
                    <p style="margin:0 0 12px;font-size:22px;font-weight:700;color:#0f172a;">
                        {{ $scoreDisplay }}
                        <span style="font-size:14px;font-weight:600;color:#64748b;">· {{ $tierLabel }}</span>
                    </p>
                    @if ($primaryNudge)
                        <p style="margin:0 0 8px;font-size:14px;line-height:1.55;color:#334155;">
                            {{ $primaryNudge }}
                        </p>
                    @endif
                    @foreach ($secondaryLines as $line)
                        <p style="margin:0 0 4px;font-size:13px;line-height:1.5;color:#64748b;">
                            {{ $line }}
                        </p>
                    @endforeach
                </td>
            </tr>
        </table>
    @endif

    @if ($isDueDay || ! $isBeforeDue)
        <div style="margin:24px 0 0;">
            <x-mail.button :url="$portalUrl" :label="$confirmPaymentCta" />
        </div>
        <p style="margin:12px 0 0;font-size:13px;line-height:1.55;color:#64748b;">
            Paid by bank transfer? Upload your receipt in the portal — no card needed.
        </p>
    @endif

    <p style="margin:24px 0 0;font-size:14px;line-height:1.65;color:#64748b;">
        Questions? Contact <strong style="color:#334155;">{{ $landlordName }}</strong> directly.
    </p>

    <p style="margin:16px 0 0;font-size:14px;line-height:1.65;color:#64748b;">
        Thank you,<br>
        <strong style="color:#334155;">{{ $landlordName }}</strong>
        <span style="color:#94a3b8;">via {{ $appName }}</span>
    </p>
@endsection
