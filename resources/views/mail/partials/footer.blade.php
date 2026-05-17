<tr>
    <td align="center" style="padding:24px 16px 0;">
        <p style="margin:0 0 8px;font-size:12px;line-height:1.6;color:#64748b;">
            &copy; {{ date('Y') }} {{ $appName ?? config('app.name') }}. All rights reserved.
        </p>
        @if (! empty($supportEmail))
            <p style="margin:0;font-size:12px;line-height:1.6;color:#64748b;">
                Questions? <a href="mailto:{{ $supportEmail }}" style="color:{{ $brandColor ?? config('landlord.mail.brand_color') }};text-decoration:none;">{{ $supportEmail }}</a>
            </p>
        @endif
        @isset($appUrl)
            <p style="margin:12px 0 0;font-size:11px;color:#94a3b8;">
                <a href="{{ $appUrl }}" style="color:#94a3b8;text-decoration:underline;">{{ parse_url($appUrl, PHP_URL_HOST) }}</a>
            </p>
        @endisset
    </td>
</tr>
