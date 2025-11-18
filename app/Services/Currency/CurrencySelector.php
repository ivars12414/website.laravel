<?php

namespace App\Services\Currency;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class CurrencySelector
{
    public const COOKIE_KEY = 'currency';

    public function resolve(Request $request): Currency
    {
        $currency = $this->resolveFromQuery($request)
            ?? $this->resolveFromProfile()
            ?? $this->resolveFromCookie($request)
            ?? Currency::main();

        $this->rememberSelection($request, $currency);

        return $currency;
    }

    protected function resolveFromQuery(Request $request): ?Currency
    {
        $code = $request->query('currency');

        return $this->findActiveCurrency($code);
    }

    protected function resolveFromProfile(): ?Currency
    {
        $user = Auth::guard('client')->user() ?? Auth::user();
        $code = $user?->currency ?? $user?->currency_code ?? null;

        return $this->findActiveCurrency($code);
    }

    protected function resolveFromCookie(Request $request): ?Currency
    {
        $code = $request->cookie(self::COOKIE_KEY);

        return $this->findActiveCurrency($code);
    }

    protected function rememberSelection(Request $request, Currency $currency): void
    {
        $request->session()->put('currency', [
            'code' => $currency->code,
            'value' => $currency->value,
            'decimals' => $currency->decimals,
            'symbol' => $currency->symbol,
            'symbol_position' => $currency->symbol_position,
            'thousands_separator' => $currency->thousands_separator,
        ]);

        $minutes = config('session.lifetime');

        Cookie::queue(
            cookie(
                self::COOKIE_KEY,
                $currency->code,
                $minutes,
                '/',
                null,
                (bool) config('session.secure'),
                true,
                false,
                config('session.same_site')
            )
        );
    }

    protected function findActiveCurrency(?string $code): ?Currency
    {
        if (!$code) {
            return null;
        }

        return Currency::where('status', '1')->where('code', $code)->first();
    }
}
