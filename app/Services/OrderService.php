<?php

namespace App\Services;

use App\Models\Contacts;
use App\Models\Mail;
use App\Models\Order;
use App\Models\Status;
use App\Models\StatusLog;
use Dompdf\Dompdf;

class OrderService
{
    public function updateOrderStatus(Order $order, Status $status, int $source, array $add_data = []): array
    {
        // Обновление статуса
        $order->status()->associate($status);
        $order->save();

        switch ($status->label) {
            case 'created':
            case 'accepted':
            case 'completed':
                if (!isStatusInLog('completed', $order->id)) {
                    $this->sendMailToClient($order, 'order_completed');
                }
            case 'canceled':
            case 'deleted':
                break;
        }

        // Запись в лог
        StatusLog::logChange(
            $order->id,
            $status,
            (int)$_SESSION['login_id'],
            $source,
            $add_data['reason_hash'] ?? '',
            $add_data
        );

        return ['error' => false, 'msg' => 'Order status changed'];
    }

    public function generateInvoice(Order $order): Order
    {
        $folder = "/userfiles/orders/pdf";

        $transaction = $order;

        try {
            $contacts = Contacts::where('lang_id', $transaction->lang_id)->first();

            if (!empty($transaction->pdf)) {
                @unlink(base_path($transaction->pdf));
            }

            ob_start();
            include base_path("/modules/pdf_html/order.php");
            $html = ob_get_contents();
            ob_clean();
            ob_end_clean();

            $tmp = sys_get_temp_dir();
            $dompdf = new Dompdf([
                'logOutputFile' => '',
                // authorize DomPdf to download fonts and other Internet assets
                'isRemoteEnabled' => true,
                // all directories must exist and not end with /
                'fontDir' => $tmp,
                'fontCache' => $tmp,
                'tempDir' => $tmp,
                'chroot' => $tmp,
            ]);
            $options = $dompdf->getOptions();
            $options->setDefaultFont('helvetica');
            $dompdf->setOptions($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $output = $dompdf->output();

            $fname = $transaction->nr . ".pdf";

            $addFolder = date("/Y/m/");
            if (!is_dir(base_path($folder . $addFolder))) {
                mkdir(base_path($folder . $addFolder), 0775, true);
            }
            $fname = $folder . $addFolder . $fname;
            file_put_contents(base_path($fname), $output);

            $transaction->update(['pdf' => $fname]);
        } catch (\Exception $exception) {

        }

        return $transaction;
    }

    public function sendMailToClient(Order $order, $blade = 'order_confirm'): array
    {
        $to = $order->client->mail;
//    $to = 'test-6dubxk0hg@srv1.mail-tester.com';

        $mail_vars = [
            '%client_name%' => $order->client->name,
            '%order_nr%' => $order->nr,
            '%order_sum%' => currency($order->getTotal())->convert($order->currency_code)->format(withStrongInt: false),
        ];

        if (empty($order->pdf)) {
            $this->generateInvoice($order);
        }
        $pdf[] = $_SERVER['DOCUMENT_ROOT'] . $order->pdf;
        return Mail::send_mail_template($order->lang_id, $blade, $to, $mail_vars, $pdf);
    }

    public function findOrderByEsimOrderNo($orderNr, $esimOrderNr)
    {
        return Order::where('nr', $orderNr)->where('esim_order_nr', $esimOrderNr)->first();
    }
}
