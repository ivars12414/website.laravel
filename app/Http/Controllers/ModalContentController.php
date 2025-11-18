<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ModalContentController extends Controller
{
    public function __invoke(Request $request, string $action): JsonResponse|Response
    {
        return match ($action) {
            'auth/login' => $this->login(),
            default => response()->json(['message' => 'Modal content not found'], 404),
        };
    }

    protected function login(): JsonResponse
    {
        return response()->json([
            'html' => view('modals.auth.login')->render(),
        ]);
    }
}
