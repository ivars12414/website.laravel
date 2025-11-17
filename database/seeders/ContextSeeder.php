<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Section;
use Illuminate\Database\Seeder;

class ContextSeeder extends Seeder
{
    public function run(): void
    {
        $en = Language::firstOrCreate(['code' => 'en'], ['name' => 'English', 'main' => true, 'status' => 1]);
        $lv = Language::firstOrCreate(['code' => 'lv'], ['name' => 'Latviešu', 'main' => false, 'status' => 1]);

        foreach ([$en, $lv] as $language) {
            Section::firstOrCreate([
                'hash' => $language->code . '-home',
            ], [
                'lang_id' => $language->id,
                'code' => 'home',
                'name' => 'Home',
                'default_controller' => \App\Http\Controllers\TextSectionController::class,
                'requires_auth' => false,
                'default_title' => 'Home',
                'default_h1' => 'Home',
                'position' => Section::POSITION_HEADER,
                'status' => 1,
            ]);

            Section::firstOrCreate([
                'hash' => $language->code . '-catalog',
            ], [
                'lang_id' => $language->id,
                'code' => 'catalog',
                'name' => 'Catalog',
                'default_controller' => \App\Http\Controllers\CatalogController::class,
                'requires_auth' => false,
                'default_title' => 'Catalog',
                'default_h1' => 'Catalog',
                'position' => Section::POSITION_HEADER,
                'status' => 1,
            ]);
        }
    }
}
