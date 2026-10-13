<!doctype html>
<html lang="sq">
<head>
    <meta charset="utf-8">
    <title>Përditësim i porosisë</title>
</head>

<body style="margin:0;background:#f4f7f8;color:#0b1220;font-family:Arial,Helvetica,sans-serif;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0"
       style="background:#f4f7f8;padding:24px 0;">
    <tr>
        <td align="center">

            <table role="presentation" width="640" cellpadding="0" cellspacing="0"
                   style="width:640px;max-width:94%;background:#ffffff;border:1px solid #d8e1e7;border-radius:12px;overflow:hidden;">

                <tr>
                    <td style="background:#090f1d;color:#ffffff;padding:24px 28px;">
                        <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:#cbd5e1;">
                            EcoFlow Kosovo
                        </p>

                        <h1 style="margin:0;font-size:26px;">
                            Përditësim për porosinë tuaj
                        </h1>
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px;">

                        <p style="font-size:15px;line-height:1.7;">
                            Përshëndetje,
                        </p>

                        <p style="font-size:15px;line-height:1.7;">
                            Statusi i porosisë suaj
                            <strong>{{ $order->order_number }}</strong>
                            është përditësuar.
                        </p>

                        <div style="
                            background:#f8fafc;
                            border:1px solid #d8e1e7;
                            border-radius:10px;
                            padding:18px;
                            margin:22px 0;
                        ">
                            <div style="font-size:13px;color:#64748b;margin-bottom:5px;">
                                Statusi aktual
                            </div>

                            <strong style="font-size:18px;">
                                {{ ucfirst($order->status) }}
                            </strong>
                        </div>

                        @if($order->admin_note)
                            <p style="font-size:14px;line-height:1.7;color:#475569;">
                                {{ $order->admin_note }}
                            </p>
                        @endif

                        <p style="font-size:14px;line-height:1.7;color:#475569;">
                            Do t'ju njoftojmë përsëri sapo të ketë një përditësim të ri për porosinë tuaj.
                        </p>

                        <p style="margin-top:28px;font-size:14px;">
                            Faleminderit,<br>
                            <strong>EcoFlow Kosovo</strong>
                        </p>

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>