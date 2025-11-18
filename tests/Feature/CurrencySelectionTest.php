<?php

use App\Models\Client;
use App\Models\Currency;
use App\Models\Language;
use App\Models\Section;
use App\Support\PageContext;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('sections');
    Schema::dropIfExists('langs');
    Schema::dropIfExists('currencies');
    Schema::dropIfExists('cl_clients');

    Schema::create('currencies', function (Blueprint $table) {
        $table->increments('id');
        $table->string('code')->unique();
        $table->decimal('value', 10, 4)->default(1);
        $table->unsignedTinyInteger('decimals')->default(2);
        $table->string('symbol')->default('$');
        $table->string('symbol_position')->default('left');
        $table->string('thousands_separator')->default(' ');
        $table->string('country_code')->nullable();
        $table->boolean('is_main')->default(false);
        $table->boolean('status')->default(true);
    });

    Schema::create('langs', function (Blueprint $table) {
        $table->increments('id');
        $table->string('code');
        $table->boolean('status')->default(true);
        $table->boolean('main')->default(false);
    });

    Schema::create('sections', function (Blueprint $table) {
        $table->increments('id');
        $table->string('code');
        $table->string('hash');
        $table->unsignedInteger('lang_id');
        $table->boolean('main')->default(false);
        $table->integer('position')->default(0);
        $table->boolean('hide_in_menu')->default(false);
        $table->integer('order_id')->default(0);
        $table->integer('bottom_order_id')->default(0);
        $table->boolean('auth_required')->default(false);
        $table->string('controller')->nullable();
        $table->string('name')->nullable();
        $table->string('name2')->nullable();
        $table->string('default_title')->nullable();
        $table->string('default_description')->nullable();
        $table->string('default_h1')->nullable();
        $table->text('meta_extra')->nullable();
        $table->boolean('status')->default(true);
    });

    Schema::create('cl_clients', function (Blueprint $table) {
        $table->increments('id');
        $table->string('mail')->unique();
        $table->string('password');
        $table->string('name')->nullable();
        $table->string('surname')->nullable();
        $table->string('currency')->nullable();
        $table->string('currency_code')->nullable();
        $table->float('balance')->default(0);
        $table->float('neurons')->default(0);
        $table->timestamp('reg_tm')->nullable();
        $table->timestamp('deleted_at')->nullable();
    });

    $en = Language::create(['code' => 'en', 'status' => 1, 'main' => 1]);
    $de = Language::create(['code' => 'de', 'status' => 1, 'main' => 0]);

    foreach ([$en, $de] as $language) {
        Section::create([
            'code' => 'home',
            'hash' => 'home',
            'lang_id' => $language->id,
            'main' => 1,
            'position' => 0,
            'hide_in_menu' => 0,
            'order_id' => 0,
            'bottom_order_id' => 0,
            'auth_required' => 0,
            'name' => 'Home',
            'name2' => 'Home',
            'default_title' => 'Home',
            'default_description' => 'Home',
            'default_h1' => 'Home',
            'meta_extra' => json_encode([]),
            'status' => 1,
        ]);
    }

    Currency::create([
        'code' => 'USD',
        'value' => 1,
        'decimals' => 2,
        'symbol' => '$',
        'symbol_position' => 'left',
        'thousands_separator' => ' ',
        'is_main' => 1,
        'status' => 1,
    ]);

    Currency::create([
        'code' => 'EUR',
        'value' => 0.9,
        'decimals' => 2,
        'symbol' => '€',
        'symbol_position' => 'right',
        'thousands_separator' => ' ',
        'is_main' => 0,
        'status' => 1,
    ]);

    Route::middleware('web')->get('/{lang?}/context-check', function (PageContext $context) {
        return response()->json([
            'currency' => $context->currency()?->code,
            'language' => $context->language()?->code,
            'section' => $context->section()?->code,
        ]);
    })->where('lang', '[a-z]{2}');
});

it('prefers explicit query selection', function () {
    $response = $this->get('/en/context-check?currency=EUR');

    $response->assertOk();
    $response->assertJson(['currency' => 'EUR']);
    expect(session('currency.code'))->toBe('EUR');
    $response->assertCookie('currency', 'EUR');
});

it('falls back to default currency when nothing is chosen', function () {
    $response = $this->get('/context-check');

    $response->assertOk();
    $response->assertJson(['currency' => 'USD']);
    expect(session('currency.code'))->toBe('USD');
});

it('uses profile currency when user or locale changes', function () {
    $client = Client::create([
        'mail' => 'user@example.com',
        'password' => Hash::make('password'),
        'currency' => 'EUR',
    ]);

    $response = $this->withCookie('currency', 'USD')
        ->actingAs($client, 'client')
        ->get('/de/context-check');

    $response->assertOk();
    $response->assertJson(['currency' => 'EUR', 'language' => 'de']);
    expect(session('currency.code'))->toBe('EUR');
});
