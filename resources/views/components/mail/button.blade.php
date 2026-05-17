@props([
    'url',
    'label',
    'color' => null,
])

@php
    $bg = $color ?? ($brandColor ?? config('landlord.mail.brand_color'));
@endphp

<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:28px 0;">
    <tr>
        <td align="center" style="border-radius:8px;background-color:{{ $bg }};">
            <a href="{{ $url }}" target="_blank" style="display:inline-block;padding:14px 28px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px;background-color:{{ $bg }};">
                {{ $label }}
            </a>
        </td>
    </tr>
</table>
