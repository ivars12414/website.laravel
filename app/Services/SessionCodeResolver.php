<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionCodeResolver
{
    public const SESSION_KEY = 'session_code';
    public const SESSION_USER_KEY = 'session_code_user_id';

    public function resolve(Request $request): string
    {
        $session = $request->session();
        $currentUserKey = $this->resolveUserKey();
        $existing = $session->get(self::SESSION_KEY);
        $existingUserKey = $session->get(self::SESSION_USER_KEY);

        if (!empty($existing) && $existingUserKey === $currentUserKey) {
            $this->queueCookie($existing);

            return (string) $existing;
        }

        $sessionCode = $this->generateCode();

        $session->put(self::SESSION_KEY, $sessionCode);
        $session->put(self::SESSION_USER_KEY, $currentUserKey);

        $this->queueCookie($sessionCode);

        return $sessionCode;
    }

    protected function resolveUserKey(): string
    {
        $userId = Auth::id();

        return (string) ($userId ?? 'guest');
    }

    protected function generateCode(): string
    {
        // Opaque, non-identifying value safe to expose to the client.
        return bin2hex(random_bytes(32));
    }

    protected function queueCookie(string $sessionCode): void
    {
        $minutes = config('session.lifetime');

        cookie()->queue(
            cookie(
                self::SESSION_KEY,
                $sessionCode,
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
}
