<?php

namespace App\Rules;

use App\Services\CreditService;
use Illuminate\Contracts\Validation\ValidationRule;

class MaxTopUpAmount implements ValidationRule
{
    protected int|float $required;

    public function __construct($required)
    {
        $this->required = $required;
    }

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $max_credits_amount = !empty(getConfig('top_up_max_amount')) ? getConfig('top_up_max_amount') : CreditService::convertFromFiat(1000);
        if ($max_credits_amount < $this->required) {
            $fail(returnWord('Max amount is %AMOUNT%', WORDS_PROJECT, ['%AMOUNT%' => $max_credits_amount]));
        }
    }
}
