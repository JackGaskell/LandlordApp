@props(['label' => null])

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin:24px 0;background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
    <tr>
        <td style="padding:20px 24px;">
            @if ($label)
                <p style="margin:0 0 12px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#64748b;">
                    {{ $label }}
                </p>
            @endif
            {{ $slot }}
        </td>
    </tr>
</table>
