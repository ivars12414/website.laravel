<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Mail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ResendActivationCodeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $mail = trim((string)$request->input('client_mail', $request->input('mail')));

        if ($mail === '') {
            return $this->errorResponse('Empty mail', 400);
        }

        $client = $this->findClient($mail);

        if (!$client) {
            return $this->errorResponse('User does not exist', 404);
        }

        $mailVars = [
            '%activation_code%' => $client->act_code,
        ];

        Mail::send_mail_template(lang()?->id ?? getMainLang(), 'resend_activation_code', $client->mail, $mailVars);

        return response()->json([
            'error' => false,
            'class' => 'alert--green',
            'msg' => returnWord('Activation code has been sent', WORDS_INTERFACE),
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
                'client_mail' => [
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
