<?php

namespace App\Http\Requests;

use App\Rules\HasEnoughBalance;
use App\Rules\MaxTopUpAmount;
use App\Rules\MinTopUpAmount;
use App\Rules\PaymentMethodExists;
use App\Services\CreditService;

class StoreTopUpRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                new HasEnoughBalance(), // теперь без конструктора
                new MinTopUpAmount(),
                new MaxTopUpAmount(),
            ],
            'payment_method' => [
                'required',
                new PaymentMethodExists($this->payment_method),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $raw = (string)$this->input('amount');

        $normalized = str_replace(' ', '', $raw);
        $normalized = str_replace(',', '.', $normalized);

        if (!is_numeric($normalized)) {
            // оставляем как есть -> rules на amount упадут с numeric
            return;
        }

        $amount = (float)$normalized;

        $this->merge([
            'amount' => $amount,
            // внутреннее поле оставляем, чтобы потом можно было использовать в сервисах/контроллере
            'amount_converted' => CreditService::convert($amount),
        ]);
    }
}
