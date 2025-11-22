<?php

namespace App\Http\Requests;

use App\Rules\HasEnoughBalance;
use App\Rules\MaxTopUpAmount;
use App\Rules\MinTopUpAmount;
use App\Rules\PaymentMethodExists;
use App\Services\CreditService;
use Illuminate\Foundation\Http\FormRequest;

class StoreTopUpRequest extends FormRequest
{

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric'],
            'amount_converted' => [
                new HasEnoughBalance($this->amount_converted),
                new MinTopUpAmount($this->amount_converted),
                new MaxTopUpAmount($this->amount_converted),
            ],
            'payment_method' => ['required', new PaymentMethodExists($this->payment_method)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $converted = CreditService::convert($this->input('amount'));
        $this->merge([
            'amount_converted' => $converted
        ]);
    }

}
