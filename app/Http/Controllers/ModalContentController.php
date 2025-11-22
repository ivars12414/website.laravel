<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ModalContentController extends Controller
{
    public function __invoke(Request $request, string $action): JsonResponse|Response
    {
        return match ($action) {
            'auth/login' => $this->login(),
            'auth/activation' => $this->activation($request),
            'credits/top_up' => $this->topUp($request),
            default => response()->json(['message' => 'Modal content not found'], 404),
        };
    }

    protected function login(): JsonResponse
    {
        return response()->json([
            'html' => view('modals.auth.login')->render(),
        ]);
    }

    protected function activation(Request $request): JsonResponse
    {
        $mail = filter_var((string)$request->input('mail'), FILTER_SANITIZE_EMAIL);

        return response()->json([
            'html' => view('modals.auth.activation', [
                'mail' => $mail,
                'seconds' => ACTIVATION_CODE_RESEND_COUNTDOWN,
            ])->render(),
        ]);
    }

    protected function topUp(Request $request): JsonResponse
    {
        return response()->json([
            'html' => view('modals.credits.top_up', [
                'payment_methods_block' => new PaymentService()->paymentMethodBlock(Payment::ORDER_TYPE_TOP_UP),
            ])->render(),
        ]);
    }

}
