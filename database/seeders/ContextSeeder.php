<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Section;
use Illuminate\Database\Seeder;

class ContextSeeder extends Seeder
{
    public function run(): void
    {
        $en = Language::firstOrCreate(
            ['code' => 'en'],
            ['name' => 'English', 'main' => true, 'status' => true]
        );
        $lv = Language::firstOrCreate(
            ['code' => 'lv'],
            ['name' => 'Latviešu', 'main' => false, 'status' => true]
        );

        Section::firstOrCreate(
            ['code' => 'home', 'lang_id' => $en->id],
            [
                'hash' => 'home',
                'name' => 'Home',
                'default_controller' => \App\Http\Controllers\TextSectionController::class,
                'requires_auth' => false,
                'default_title' => 'Home',
                'status' => true,
            ]
        );

        Section::firstOrCreate(
            ['code' => 'catalog', 'lang_id' => $en->id],
            [
                'hash' => 'catalog',
                'name' => 'Catalog',
                'default_controller' => \App\Http\Controllers\CatalogController::class,
                'requires_auth' => false,
                'default_title' => 'Catalog',
                'status' => true,
            ]
        );

        Section::firstOrCreate(
            ['code' => 'home', 'lang_id' => $lv->id],
            [
                'hash' => 'home-lv',
                'name' => 'Sākumlapa',
                'default_controller' => \App\Http\Controllers\TextSectionController::class,
                'requires_auth' => false,
                'default_title' => 'Home',
                'status' => true,
            ]
        );

        Section::firstOrCreate(
            ['code' => 'catalog', 'lang_id' => $lv->id],
            [
                'hash' => 'catalog-lv',
                'name' => 'Katalogs',
                'default_controller' => \App\Http\Controllers\CatalogController::class,
                'requires_auth' => false,
                'default_title' => 'Catalog',
                'status' => true,
            ]
        );
    }
}
