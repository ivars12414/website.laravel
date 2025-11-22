<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class HasEnoughBalance implements ValidationRule
{
    protected int|float $required;

    public function __construct($required)
    {
        $this->required = $required;
    }

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $user = auth()->user();

        if (!$user) {
            $fail('Пользователь не авторизован.');
            return;
        }

        $balance = $user->balance; // или ->credits_balance — как у тебя поле называется

        if ($balance < $this->required) {
            $fail('Недостаточно средств на балансе.');
        }
    }
}
