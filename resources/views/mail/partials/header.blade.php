<tr>
    <td align="center" style="background-color:{{ $brandColor ?? config('landlord.mail.brand_color') }};border-radius:12px 12px 0 0;padding:28px 32px;">
        <p style="margin:0;font-size:20px;font-weight:700;color:#ffffff;letter-spacing:-0.02em;">
            {{ $appName ?? config('app.name') }}
        </p>
        @isset($title)
            <p style="margin:8px 0 0;font-size:14px;font-weight:500;color:rgba(255,255,255,0.88);">
                {{ $title }}
            </p>
        @endisset
    </td>
</tr>
