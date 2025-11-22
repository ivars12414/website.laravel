<?php

namespace App\Rules;

use App\Services\CreditService;
use Illuminate\Contracts\Validation\ValidationRule;

class MaxTopUpAmount implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        // максималка в кредитах
        $maxCreditsAmount = !empty(getConfig('top_up_max_amount'))
            ? (float)getConfig('top_up_max_amount')
            : (float)CreditService::convertFromFiat(1000);

        $converted = CreditService::convert((float)$value);

        if ($converted > $maxCreditsAmount) {
            $fail(returnWord(
                'Max amount is %AMOUNT%',
                WORDS_PROJECT,
                ['%AMOUNT%' => $maxCreditsAmount]
            ));
        }
    }
}
