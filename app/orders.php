<?php

use App\Models\Status;
use App\Models\StatusLog;

function changeOrderStatus(int $orderID, string $statusID, string $source, string $reasonHash = ''): array
{
    global $langId;

    $status = Status::find($statusID);
    $reasonData = !empty($reasonHash)
        ? getWhat('status_reason', "WHERE `hash` = '$reasonHash' AND lang_id='$langId'")
        : [];
    $orderData = getWhat('order', "WHERE `id` = '$orderID'");

    $error = false;
    $msg = '';

    if ($orderData['id'] > 0) {

        // Отдельная логика склада для каждого статуса
        switch ($status->label) {
            case 'created':
            case 'accepted':
            case 'completed':
                if (!isStatusInLog('completed', $orderData['id'])) {
                    // отправка письма клиенту
                }
            case 'canceled':
            case 'deleted':
                break;
        }

//    if (!empty($reasonHash)) {
//      switch ($reasonData['label']) {
//        case 'by_client':
//          break;
//      }
//    }


        if (!$error) {

            $error = false;
            $msg = 'Order status changed';

            \App\Models\Order::find($orderID)->update(['status_id' => $statusID]);

            // Записываем лог
            insertOrderStatusLog($orderID, $statusID, $source, $reasonHash);

        }

    } else {
        $error = true;
        $msg = 'Not valid order ID';
    }

    return [
        'error' => $error,
        'msg' => $msg,
    ];
}

function insertOrderStatusLog(int $orderID, int $statusID, string $source, string $reasonHash = ''): void
{
    $user_id = $source === SOURCE_ADMIN ? (int)$_SESSION['user_id'] : 0;
    StatusLog::logChange($orderID, Status::find($statusID), $user_id, $source, $reasonHash);
}

function isStatusInLog(string $label, int $orderID): bool
{
    $status = Status::findByLabel($label, 'orders');
    return !empty($status->id) && StatusLog::where('order_id', $orderID)->where('status_id', $status->id)->exists();
}
