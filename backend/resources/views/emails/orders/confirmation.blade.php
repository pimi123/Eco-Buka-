<!doctype html>
<html lang="sq">
<head>
    <meta charset="utf-8">
    <title>Konfirmimi i porosisë</title>
</head>
<body style="margin:0;background:#f4f7f8;color:#0b1220;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7f8;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellpadding="0" cellspacing="0" style="width:640px;max-width:94%;background:#ffffff;border:1px solid #d8e1e7;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background:#090f1d;color:#ffffff;padding:24px 28px;">
                            <p style="margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#cbd5e1;">Eco Buka</p>
                            <h1 style="margin:0;font-size:26px;line-height:1.2;">Porosia juaj u pranua</h1>
                            <p style="margin:10px 0 0;font-size:15px;line-height:1.6;color:#e2e8f0;">Faleminderit për porosinë. Më poshtë janë detajet që kemi regjistruar.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:26px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:22px;">
                                <tr>
                                    <td style="font-size:14px;line-height:1.7;color:#475569;">
                                        <strong style="color:#0b1220;">Numri i porosisë:</strong> {{ $order->order_number }}<br>
                                        <strong style="color:#0b1220;">Statusi:</strong> {{ ucfirst($order->status) }}<br>
                                        <strong style="color:#0b1220;">Data:</strong> {{ optional($order->created_at)->format('d.m.Y H:i') }}<br>
                                        <strong style="color:#0b1220;">Totali:</strong> {{ number_format((float) $order->total, 2) }} {{ $order->currency }}
                                    </td>
                                </tr>
                            </table>

                            <h2 style="margin:0 0 12px;font-size:18px;">Produktet e porositura</h2>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-bottom:24px;">
                                <thead>
                                    <tr>
                                        <th align="left" style="border-bottom:1px solid #d8e1e7;padding:10px 0;font-size:12px;text-transform:uppercase;color:#64748b;">Produkti</th>
                                        <th align="center" style="border-bottom:1px solid #d8e1e7;padding:10px 0;font-size:12px;text-transform:uppercase;color:#64748b;">Sasia</th>
                                        <th align="right" style="border-bottom:1px solid #d8e1e7;padding:10px 0;font-size:12px;text-transform:uppercase;color:#64748b;">Shuma</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->items as $item)
                                        <tr>
                                            <td style="border-bottom:1px solid #eef2f5;padding:12px 0;font-size:14px;line-height:1.5;">
                                                <strong>{{ $item->product_name }}</strong>
                                                @if (! empty($item->product_snapshot['category']))
                                                    <br><span style="color:#64748b;">{{ $item->product_snapshot['category'] }}</span>
                                                @endif
                                                @if (! empty($item->product_snapshot['specs']) && is_array($item->product_snapshot['specs']))
                                                    <br><span style="color:#64748b;">
                                                        @foreach (array_slice($item->product_snapshot['specs'], 0, 3, true) as $label => $value)
                                                            {{ $label }}: {{ $value }}@if (! $loop->last), @endif
                                                        @endforeach
                                                    </span>
                                                @endif
                                            </td>
                                            <td align="center" style="border-bottom:1px solid #eef2f5;padding:12px 0;font-size:14px;">{{ $item->quantity }}</td>
                                            <td align="right" style="border-bottom:1px solid #eef2f5;padding:12px 0;font-size:14px;font-weight:700;">{{ number_format((float) $item->line_total, 2) }} {{ $order->currency }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <h2 style="margin:0 0 12px;font-size:18px;">Dërgesa</h2>
                            <p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:#475569;">
                                {{ $order->customer_name }}<br>
                                {{ $order->customer_phone }}<br>
                                {{ $order->delivery_address }}, {{ $order->municipality ?: $order->city }}@if($order->postal_code), {{ $order->postal_code }}@endif<br>
                                {{ $order->country }}
                            </p>

                            <div style="background:#f8fafc;border:1px solid #d8e1e7;border-radius:10px;padding:16px;margin:0 0 18px;">
                                <strong style="display:block;margin-bottom:6px;">Koha e arritjes dhe konfirmimi final</strong>
                                <p style="margin:0;font-size:14px;line-height:1.7;color:#475569;">
                                    Ekipi Eco Buka do t'ju kontaktojë për të konfirmuar disponueshmërinë, mënyrën e dërgesës dhe afatin final të arritjes. Produktet me para-porosi ose konfigurim të veçantë konfirmohen veçmas para dorëzimit.
                                </p>
                            </div>

                            <p style="margin:0;font-size:13px;line-height:1.7;color:#64748b;">
                                Ky email shërben si dëshmi fillestare e porosisë dhe e të dhënave të regjistruara. Për pyetje, na kontaktoni në ecobuka.ks@gmail.com.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
