<?php

use App\Services\SessionCodeResolver;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $session = app('session')->driver();
    $session->start();
    session()->setId($session->getId());
    session()->start();
});

test('session code persists within the same session', function () {
    $resolver = app(SessionCodeResolver::class);

    $request = Request::create('/test');
    $request->setLaravelSession(session());

    $first = $resolver->resolve($request);
    $second = $resolver->resolve($request);

    expect($first)->toMatch('/^[a-f0-9]{64}$/');
    expect($first)->toBe($second);
    expect(session(SessionCodeResolver::SESSION_KEY))->toBe($first);
    expect(session(SessionCodeResolver::SESSION_USER_KEY))->toBe('guest');
});

test('session code regenerates when user context changes', function () {
    $resolver = app(SessionCodeResolver::class);

    $request = Request::create('/test');
    $request->setLaravelSession(session());

    $guestCode = $resolver->resolve($request);

    Auth::guard('web')->setUser(new GenericUser(['id' => 42]));
    $request->setUserResolver(fn () => Auth::user());

    $userCode = $resolver->resolve($request);

    expect($userCode)->not->toBe($guestCode);
    expect(session(SessionCodeResolver::SESSION_USER_KEY))->toBe('42');

    $repeatUserCode = $resolver->resolve($request);
    expect($repeatUserCode)->toBe($userCode);

    Auth::guard('web')->logout();
    $request->setUserResolver(fn () => null);

    $guestAgain = $resolver->resolve($request);

    expect($guestAgain)->not->toBe($userCode);
    expect(session(SessionCodeResolver::SESSION_USER_KEY))->toBe('guest');
});
