<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ActivationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $activationCode = trim((string)$request->input('act_code'));
        $mail = trim((string)$request->input('client_mail'));

        if ($activationCode === '') {
            return $this->errorResponse('Empty activation code', 400);
        }

        if ($mail === '') {
            return $this->errorResponse('Empty mail', 400);
        }

        $client = $this->findClient($mail);

        if (!$client) {
            return $this->errorResponse('User does not exist', 400);
        }

        if ($client->act_code !== $activationCode) {
            return $this->errorResponse('Invalid activation code', 400);
        }

        $client->status = 1;
        $client->save();

        Auth::shouldUse('client');
        Auth::guard('client')->login($client);
        session(['login_id' => $client->id]);
        $_SESSION['login_id'] = $client->id;

        $redirectHref = $request->input('success_href') ?: sectionHref('profile');

        return response()->json([
            'error' => false,
            'class' => 'alert--green',
            'redirect_href' => $redirectHref,
            'msg' => returnWord('Success registration alert', WORDS_PROJECT),
        ]);
    }

    protected function findClient(string $mail): ?Client
    {
        $sanitizedMail = filter_var($mail, FILTER_SANITIZE_EMAIL);

        return Client::query()
            ->where('mail', $sanitizedMail)
            ->when(
                Schema::hasColumn('cl_clients', 'deleted'),
                fn($query) => $query->where('deleted', '0')
            )
            ->first();
    }

    protected function errorResponse(string $message, int $status): JsonResponse
    {
        return response()->json([
            'error' => true,
            'class' => 'alert--red',
            'error_fields' => [
                'act_code' => [
                    'class' => 'error',
                    'msg' => '',
                ],
            ],
            'empty_groups' => [
                'default' => returnWord($message, WORDS_INTERFACE),
            ],
        ], $status);
    }
}
