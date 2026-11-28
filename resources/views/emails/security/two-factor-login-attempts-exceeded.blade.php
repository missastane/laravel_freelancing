
@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'fa';
    $direction = $isRtl ? 'rtl' : 'ltr';

    $translation = fn ($key, $replace = []) =>
        __('emails.security.two_factor_login_attempts_exceeded.' . $key, $replace);
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $direction }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">

    <title>{{ $translation('title') }}</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f5f7;
            font-family: Arial, Tahoma, sans-serif;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }

        .content {
            background-color: #ffffff;
            padding: 40px 35px;
        }

        .details-box {
            background-color: #f7f8fa;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
        }

        .warning-box {
            background-color: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 8px;
            padding: 20px;
        }

        .detail-label {
            color: #6b7280;
            font-size: 13px;
            padding: 8px 0;
        }

        .detail-value {
            color: #111827;
            font-size: 13px;
            padding: 8px 0;
            word-break: break-word;
        }

        @media screen and (max-width: 600px) {
            .content {
                padding: 25px 18px !important;
            }
        }
    </style>
</head>

<body>
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">
        {{ $translation('preheader') }}
    </div>

    <table role="presentation" width="100%" bgcolor="#f4f5f7">
        <tr>
            <td align="center" style="padding:30px 12px;">

                <table role="presentation" class="email-container" width="600">

                    <!-- Header -->
                    <tr>
                        <td align="center"
                            style="background-color:#172554;padding:28px 20px;">
                            <h1 style="margin:0;color:#ffffff;font-size:25px;">
                                {{ config('app.name') }}
                            </h1>
                        </td>
                    </tr>

                    <!-- Main content -->
                    <tr>
                        <td class="content" dir="{{ $direction }}"
                            style="text-align:{{ $isRtl ? 'right' : 'left' }};">

                            <h2 style="color:#b45309;font-size:21px;margin:0 0 24px;">
                                {{ $translation('title') }}
                            </h2>

                            <p style="color:#111827;font-size:15px;line-height:1.9;">
                                {{ $translation('greeting', [
                                    'name' => $details['name'] ?? ''
                                ]) }}
                            </p>

                            <p style="color:#374151;font-size:14px;line-height:2;">
                                {{ $translation('body') }}
                            </p>

                            <!-- Attempt details -->
                            <h3 style="font-size:16px;color:#111827;margin:30px 0 15px;">
                                {{ $translation('details_title') }}
                            </h3>

                            <table role="presentation" width="100%"
                                class="details-box" dir="{{ $direction }}">

                                @if (!empty($details['attempted_at']))
                                    <tr>
                                        <td class="detail-label" width="38%">
                                            {{ $translation('attempted_at') }}
                                        </td>
                                        <td class="detail-value">
                                            {{ $details['attempted_at'] }}
                                        </td>
                                    </tr>
                                @endif

                                @if (!empty($details['ip_address']))
                                    <tr>
                                        <td class="detail-label">
                                            {{ $translation('ip_address') }}
                                        </td>
                                        <td class="detail-value" dir="ltr">
                                            {{ $details['ip_address'] }}
                                        </td>
                                    </tr>
                                @endif

                                @if (!empty($details['device']))
                                    <tr>
                                        <td class="detail-label">
                                            {{ $translation('device') }}
                                        </td>
                                        <td class="detail-value" dir="ltr">
                                            {{ $details['device'] }}
                                        </td>
                                    </tr>
                                @endif

                                @if (!empty($details['client_type']))
                                    <tr>
                                        <td class="detail-label">
                                            {{ $translation('client_type') }}
                                        </td>
                                        <td class="detail-value">
                                            {{ $details['client_type'] === 'mobile'
                                                ? $translation('client_mobile')
                                                : $translation('client_web') }}
                                        </td>
                                    </tr>
                                @endif

                            </table>

                            <!-- Security warning -->
                            <div class="warning-box" style="margin-top:30px;">

                                <h3 style="color:#9a3412;font-size:15px;margin:0 0 12px;">
                                    {{ $translation('warning_title') }}
                                </h3>

                                <p style="color:#7c2d12;font-size:14px;line-height:1.9;margin:0;">
                                    {{ $translation('warning_body') }}
                                </p>

                            </div>

                            <p style="color:#6b7280;font-size:13px;line-height:1.9;margin-top:25px;">
                                {{ $translation('closing') }}
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center"
                            style="background-color:#f9fafb;padding:22px 25px;">

                            <p style="color:#6b7280;font-size:12px;line-height:1.8;margin:0;">
                                {{ $translation('footer') }}
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>
