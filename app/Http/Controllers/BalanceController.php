<?php

namespace App\Http\Controllers;

use App\Models\BalanceLog;
use App\Models\CreditsTransaction;
use App\Models\Order;
use App\Services\CreditService;
use App\Support\PageContext;
use Illuminate\Http\Request;
use stdClass;

class BalanceController extends Controller
{
    public function handle(Request $request, PageContext $context)
    {
        $context->breadcrumbs([
            ['title' => $context->section()->name, 'url' => url()->current()],
        ]);

        if (!$context->meta('title')) $context->meta('title', $context->section()->name);

        $balance_log = [];
        foreach (auth()->user()->balanceLogs()->where('type', BalanceLog::TYPE_CREDIT_TRANSACTION)->orderBy('id', 'desc')->get() as $record) {
            $new_row = new StdClass();

            $new_row->id = $record->id;
            $new_row->credits = CreditService::convert($record->credits);
            $new_row->price = currency(CreditService::convert($record->credits));

            switch ($record->type) {
                case BalanceLog::TYPE_CREDIT_TRANSACTION:
                    /** @var CreditsTransaction $transaction */
                    $transaction = CreditsTransaction::find($record->type_id);
                    if (!empty($transaction)) {
                        $new_row->id = $transaction->nr;
                        $new_row->price = currency($transaction->price_in_currency, $transaction->currency_code);
                        $new_row->date = $transaction->created_at->format('d.m.Y');
                        $new_row->status = $transaction->status->name;
                        if (!empty($transaction->pdf)) {
                            $new_row->invoice_href = "/invoice.php?nr=$transaction->nr";
                        }
                    }
                    break;
                case BalanceLog::TYPE_ORDER:
                    /** @var Order $order */
                    $order = Order::find($record->type_id);
                    if (!empty($order)) {
                        $new_row->id = $order->nr;
                        $new_row->price = currency($order->getTotal() * $order->currency_rate, $order->currency_code);
                        $new_row->date = $order->created_at->format('d.m.Y');
                        $new_row->status = $order->status->name;
                        if (!empty($order->pdf)) {
                            $new_row->invoice_href = "/invoice.php?order_nr=$order->nr";
                        }
                    }
                    break;
            }

            $balance_log[] = $new_row;
        }

        return view('sections.cabinet.balance', compact('context', 'balance_log'));
    }
}
