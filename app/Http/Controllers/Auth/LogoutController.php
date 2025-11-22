<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function __invoke(Request $request): JsonResponse|RedirectResponse
    {
        return $this->handle($request);
    }

    public function handle(Request $request): JsonResponse|RedirectResponse
    {
        Auth::logout();

        $request->session()->forget('login_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $redirectHref = $request->input('success_href') ?: sectionHref();

        if ($request->expectsJson()) {
            return response()->json([
                'error' => false,
                'class' => 'alert--green',
                'redirect_href' => $redirectHref,
                'msg' => returnWord('Successful logout', WORDS_PROJECT),
            ]);
        }

        return redirect($redirectHref);
    }
}
