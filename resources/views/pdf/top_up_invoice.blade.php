<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{!! $transaction->nr !!}</title>
</head>
<body>


<style>
    {!! include resource_path('views/pdf/font.css.php') !!}

    body {
        padding: 100px 0;
        background-color: #FFFFFF;
        font-family: 'DM Sans', sans-serif;
        font-weight: 400;
        font-size: 14px;
        line-height: 1.28;
        letter-spacing: -0.05em;
        color: #000000;
    }

    * {
        margin: 0;
        padding: 0;
    }
</style>

<table
    style="width: 100%; max-width: 100%; margin: 0 auto; background-color: #FFFFFF; border-collapse: collapse; border-spacing: 0;"
    border="0">
    <tr>
        <td style="padding: 50px; vertical-align: top;">
            <table style="width: 100%; margin-bottom: 50px; border-collapse: collapse; border-spacing: 0;" border="0">
                <tbody>
                <tr>
                    <td style="width: 50%; padding-right: 10px; vertical-align: top;">
                        <img src="{!! url() !!}/images/logo.png" alt="" style="height: 36px;">
                    </td>

                    <td style="width: 50%; vertical-align: top; text-align: right;">
                        <p style="font-weight: 500; font-size: 20px; line-height: 1;">Invoice
                            #{!! $transaction->nr !!}</p>
                        <p style="font-weight: 500; font-size: 12px; line-height: 2; color: #7A7A7A;">{!! $transaction->created_at->format('d.m.Y, H:i') !!}</p>
                    </td>
                </tr>
                </tbody>
            </table>

            <table style="width: 100%; margin-bottom: 50px; border-collapse: collapse; border-spacing: 0;" border="0">
                <tbody>
                <tr>
                    <td style="vertical-align: baseline; width: 50%; padding-right: 10px;">
                        <div
                            style="margin-bottom: 10px; font-weight: 600; font-size: 14px; line-height: 1; color: #777777;">{!! $contacts->jur_name !!}
                        </div>

                        <div>
                            <p style="margin-bottom: 15px; font-weight: 400; font-size: 14px;">{!! nl2br($contacts->jur_addr) !!}</p>
                            <p style="font-weight: 700; font-size: 14px; color: #9399C5;">{!! $contacts->email2 !!}</p>
                        </div>
                    </td>

                    <td style="vertical-align: baseline; width: 50%; padding-left: 10px;">
                        <div
                            style="margin-bottom: 10px; font-weight: 600; font-size: 14px; line-height: 1; color: #777777;">
                            Bill
                            to:
                        </div>

                        <div>
                            <p style="margin-bottom: 15px; font-weight: 400; font-size: 14px;">
                                {!! $client->getFullName() !!}
                                <br>
                                {!! $client->phone !!}
                            </p>
                            <p style="font-weight: 700; font-size: 14px; color: #9399C5;">{!! $client->mail !!}</p>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>

            <table style="width: 100%; margin-bottom: 100px; border-collapse: collapse; border-spacing: 0;" border="0">
                <thead>
                <tr style="border-bottom: 4px solid #7BC6CC; font-weight: 500; font-size: 10px;">
                    <th style="width: 50%; padding-bottom: 10px; padding-right: 40px; font-weight: 400; text-transform: uppercase; text-align: left; color: #777777;">
                        description
                    </th>
                    <th style="padding-bottom: 10px; padding-left: 10px; padding-right: 40px; font-weight: 400; text-transform: uppercase; text-align: left; color: #777777;">
                        Price
                    </th>
                    <th style="padding-bottom: 10px; padding-right: 40px; font-weight: 400; text-transform: uppercase; text-align: left; color: #777777;">
                        Q-ty
                    </th>
                    <th style="padding-bottom: 10px; font-weight: 400; text-transform: uppercase; text-align: right; color: #777777;">
                        amount
                    </th>
                </tr>
                </thead>

                <tbody>
                <tr style="border-bottom: 1px solid #EFEFEF; font-size: 12px;">
                    <td style="width: 50%; padding: 15px 40px 15px 0; vertical-align: top; font-weight: 400;">
                        <strong>
                            Wallet top up
                        </strong>
                    </td>
                    <td style="padding: 15px 40px 15px 10px; vertical-align: top;">{!! currency($transaction->price_in_currency, $transaction->currency_code)->format(withStrongInt: false) !!}</td>
                    <td style="padding: 15px 40px 15px 0; vertical-align: top;">1</td>
                    <td style="padding: 15px 0 15px 0; vertical-align: top; text-align: right;">{!! currency($transaction->price_in_currency, $transaction->currency_code)->format(withStrongInt: false) !!}</td>
                </tr>
                </tbody>

                <tfoot>
                <tr>
                    <td colspan="4" style="width: 100%;">
                        <table style="width: 100%; margin-top: 30px; border-collapse: collapse; border-spacing: 0;"
                               border="0">
                            <tbody>
                            <tr>
                                <td style="width: 50%; padding: 5px 10px 5px 0; font-size: 12px;">Subtotal:</td>
                                <td style="padding: 5px 40px 5px 10px; font-size: 12px;">{!! currency($transaction->price_in_currency, $transaction->currency_code)->format(withStrongInt: false) !!}</td>
                            </tr>

                            <tr>
                                <td style="width: 50%; padding: 5px 10px 5px 0; font-size: 12px;">Total:</td>
                                <td style="padding: 5px 40px 5px 10px; font-size: 12px;">{!! currency($transaction->price_in_currency, $transaction->currency_code)->format(withStrongInt: false) !!}</td>
                            </tr>
                            </tbody>

                            <tfoot>
                            <tr>
                                <td style="width: 50%; padding: 15px 10px 5px 0; font-weight: 700; font-size: 12px;">
                                    Amount paid:
                                </td>
                                <td style="padding: 15px 40px 5px 10px; font-weight: 700; font-size: 12px;">{!! currency($transaction->price_in_currency, $transaction->currency_code)->format(withStrongInt: false) !!}</td>
                            </tr>
                            </tfoot>
                        </table>
                    </td>
                </tr>
                </tfoot>
            </table>

            <table style="width: 100%; border-collapse: collapse; border-spacing: 0;" border="0">
                <tbody>
                <tr>
                    <td style="font-weight: 500; font-size: 14px; color: #777777;">© 2025 SaySim.io All rights
                        reserved.
                    </td>
                </tr>

                <tr>
                    <td style="font-weight: 700; font-size: 14px; color: #9399C5;"><a
                            href="{!! getDomainAndHttpHost() . sectionHref('privacy') !!}"
                            style="text-decoration: none; color: #000;"
                            target="_blank">Privacy
                            policy</a></td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
