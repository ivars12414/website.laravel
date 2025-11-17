<?php

use App\Http\Middleware\ResolvePageContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

const ADMIN_FOLDER = 'adm';
DEFINE("SECTION_CABINET", 5);

DEFINE("WORDS_ADMIN", 1);
DEFINE("WORDS_PROJECT", 2); // DEFINE("WORDS_COMMON", 2);
DEFINE("WORDS_INTERFACE", 3);
const WORDS_TYPES = [
    WORDS_ADMIN => ['title_code' => 'Admin words'],
    WORDS_PROJECT => ['title_code' => 'Common words'],
    WORDS_INTERFACE => ['title_code' => 'Interface words'],
];

require_once __DIR__ . '/../app/helpers.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            ResolvePageContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
