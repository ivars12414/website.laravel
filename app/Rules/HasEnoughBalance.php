<?php

namespace App\Rules;

use App\Services\CreditService;
use Illuminate\Contracts\Validation\ValidationRule;

class HasEnoughBalance implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $user = auth()->user();

        if (!$user) {
            $fail('Пользователь не авторизован.');
            return;
        }

        // $value тут = amount (фиат), уже нормализованный float
        $requiredCredits = CreditService::convert((float)$value);

        $balance = (float)$user->balance; // или credits_balance

        if ($balance < $requiredCredits) {
            $fail('Недостаточно средств на балансе.');
        }
    }
}
