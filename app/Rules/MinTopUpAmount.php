<?php

namespace App\Rules;

use App\Services\CreditService;
use Illuminate\Contracts\Validation\ValidationRule;

class MinTopUpAmount implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        // минималка в кредитах
        $minCreditsAmount = !empty(getConfig('top_up_min_amount'))
            ? (float)getConfig('top_up_min_amount')
            : (float)CreditService::convertFromFiat(5);

        // сколько кредитов получится из введённого amount
        $converted = CreditService::convert((float)$value);

        if ($converted < $minCreditsAmount) {
            $fail(returnWord(
                'Min amount is %AMOUNT%',
                WORDS_PROJECT,
                ['%AMOUNT%' => $minCreditsAmount]
            ));
        }
    }
}
