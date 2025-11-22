<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTopUpRequest;
use App\Models\Payment;
use App\Models\Status;
use App\Payment\PaymentService;
use App\Services\CreditService;
use App\Services\Currency\CurrencyManager;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class CreditsController extends Controller
{
    public function topUp(StoreTopUpRequest $request): array
    {
        $request->validated();

        try {
            // 1) Только БД-логика внутри транзакции
            $transaction = DB::transaction(function () use ($request) {

                $credits = CreditService::convertFromFiat($request['amount']);

                $price = currency($request['amount'], CurrencyManager::current())
                    ->convert(CurrencyManager::default())
                    ->format(false);

                $priceInCurrency = currency($request['amount'], CurrencyManager::current())
                    ->format(false);

                $transaction = auth()->user()->transactions()->create([
                    'credits' => $credits,
                    'price' => $price,
                    'main_currency_code' => CurrencyManager::default()->code,
                    'price_in_currency' => $priceInCurrency,
                    'currency_code' => CurrencyManager::current()->code,
                ]);

                if (!$transaction) {
                    throw new RuntimeException('Failed to create transaction');
                }

                $statusPending = Status::findByLabel('pending', 'top_up');
                if (!$statusPending) {
                    throw new RuntimeException('Status "pending" for top_up not found');
                }

                $transaction->setStatus($statusPending, SOURCE_SITE);

                return $transaction;
            });

            // 2) Внешний сервис — ТОЛЬКО после коммита
            $paymentService = new PaymentService();

            $createPayment = $paymentService->createPayment(
                Payment::ORDER_TYPE_TOP_UP,
                $transaction->id,
                $request['payment_method'],
                $transaction->price_in_currency,
                $transaction->currency_code,
                [
                    'success' => "https://" . SITEMAP_CONFIGS['site_domain'] . sectionHref('balance') . "?tx=%tx_id%",
                    'cancel' => "https://" . SITEMAP_CONFIGS['site_domain'] . sectionHref('balance') . "?tx=%tx_id%",
                    'failed' => "https://" . SITEMAP_CONFIGS['site_domain'] . sectionHref('balance') . "?tx=%tx_id%",
                ]
            );

            if (!empty($createPayment['error'])) {
                // если нужно — тут можно отдельно пометить транзакцию failed
                throw new RuntimeException($createPayment['msg'] ?? 'Payment create error');
            }

            return [
                'error' => false,
                'href' => $createPayment['href'] ?? '',
            ];

        } catch (Throwable $e) {
            return [
                'error' => true,
                'empty_groups' => [
                    'default' => returnWord($e->getMessage(), WORDS_PROJECT),
                ],
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
            ];
        }
    }
}
