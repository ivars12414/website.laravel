<?php

namespace App\Rules;

use App\Models\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;

class PaymentMethodExists implements ValidationRule
{
    protected string $required;

    public function __construct($required)
    {
        $this->required = $required;
    }

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (!PaymentMethod::whereActive()->where('hash', $value)->where('deleted', 0)->exists()) {
            $fail(returnWord('Payment method not found', WORDS_PROJECT));
        }
    }
}
