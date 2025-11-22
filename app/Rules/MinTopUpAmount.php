<?php

namespace App\Rules;

use App\Services\CreditService;
use Illuminate\Contracts\Validation\ValidationRule;

class MinTopUpAmount implements ValidationRule
{
    protected int|float $required;

    public function __construct($required)
    {
        $this->required = $required;
    }

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $min_credits_amount = !empty(getConfig('top_up_min_amount')) ? getConfig('top_up_min_amount') : CreditService::convertFromFiat(5);

        if ($min_credits_amount > $this->required) {
            $fail(returnWord('Min amount is %AMOUNT%', WORDS_PROJECT, ['%AMOUNT%' => $min_credits_amount]));
        }
    }
}
