<?php

use App\Http\Middleware\ResolvePageContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

const IN_CMS = true;
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

const DATA_TYPES_MAPPING = [
    1 => 'Data in Total',
    2 => 'Daily Unlimited',
    3 => 'Daily Limit (Service Cut-off)',
    4 => 'Daily Full Unlimited',
];

const ACTIVATION_CODE_RESEND_COUNTDOWN = 50; //seconds

const USE_CACHE = true;

define('FORM_SUBMIT_V', USE_CACHE ? time() : 2);
define('INPUT_MASK_V', USE_CACHE ? time() : 1);
define('INPUT_VALIDATOR_V', USE_CACHE ? time() : 1);
define('MODAL_MANAGER_V', USE_CACHE ? time() : 1);
define('SCROLL_V', USE_CACHE ? time() : 1);
define('SHOW_MORE_V', USE_CACHE ? time() : 1);

const SOURCE_APPLICATION = 1;
const SOURCE_SITE = 2;
const SOURCE_INVITATION = 3;
const SOURCE_APP_SITE = 4;
const SOURCE_ADMIN = 5;

const SOURCES = [
    SOURCE_APPLICATION,
    SOURCE_SITE,
    SOURCE_INVITATION,
    SOURCE_APP_SITE,
    SOURCE_ADMIN,
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
