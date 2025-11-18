<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Mail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class LoginController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $mail = trim((string)$request->input('mail'));
        $password = (string)$request->input('password');

        if ($mail === '') {
            return $this->errorResponse('Empty mail', 'mail', 400);
        }

        if ($password === '') {
            return $this->errorResponse('Empty password', 'password', 400);
        }

        $client = $this->findClient($mail);

        if (!$client || !$this->passwordMatches($client, $password)) {
            return $this->errorResponse('Authorization error', 'mail', 401);
        }

        if ((int)($client->status ?? 0) !== 1) {
            $this->sendActivationMail($client, $request);

            return $this->errorResponse('Not activated', 'mail', 401, [
                'activation_form' => true,
                'mail' => $client->mail,
            ]);
        }

        Auth::shouldUse('client');
        Auth::guard('client')->login($client);
        session(['login_id' => $client->id]);
        $_SESSION['login_id'] = $client->id;

        $redirectHref = $request->input('success_href') ?: sectionHref('settings');

        return response()->json([
            'error' => false,
            'class' => 'alert--green',
            'redirect_href' => $redirectHref,
            'msg' => returnWord('Successful authorization ', WORDS_PROJECT),
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

    protected function passwordMatches(Client $client, string $password): bool
    {
        if (!empty($client->password) && Hash::check($password, $client->password)) {
            return true;
        }

        if (!empty($client->password) && hash_equals($client->password, md5($password))) {
            $client->password = $password;
            $client->save();
            return true;
        }

        return false;
    }

    protected function sendActivationMail(Client $client, Request $request): void
    {
        $langId = lang()?->id ?? getMainLang();

        $mailVars = [
            '%activation_code%' => $client->act_code,
        ];

        $template = $request->boolean('resend') ? 'resend_activation_code' : 'registration';

        Mail::send_mail_template($langId, $template, $client->mail, $mailVars);
    }

    protected function errorResponse(string $message, string $field, int $status, array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'error' => true,
            'class' => 'alert--red',
            'error_fields' => [
                $field => [
                    'class' => 'error',
                    'msg' => '',
                ],
            ],
            'empty_groups' => [
                'default' => returnWord($message, WORDS_INTERFACE),
            ],
        ], $extra), $status);
    }
}
