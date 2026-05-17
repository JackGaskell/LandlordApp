<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $title ?? ($appName ?? config('app.name')) }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body style="margin:0;padding:0;width:100%;background-color:#f4f6fb;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;">
    @isset($preheader)
        <div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;">
            {{ $preheader }}
        </div>
    @endisset

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f4f6fb;">
        <tr>
            <td align="center" style="padding:40px 16px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width:560px;">
                    @include('mail.partials.header')

                    <tr>
                        <td style="background-color:#ffffff;border-radius:0 0 12px 12px;padding:40px 32px 32px;box-shadow:0 1px 3px rgba(15,23,42,0.08);">
                            @yield('content')
                        </td>
                    </tr>

                    @include('mail.partials.footer')
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
