<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Section;
use Illuminate\Database\Seeder;

class ContextSeeder extends Seeder
{
    public function run(): void
    {
        $en = Language::firstOrCreate(['code'=>'en'], ['name'=>'English','is_default'=>true]);
        $lv = Language::firstOrCreate(['code'=>'lv'], ['name'=>'Latviešu','is_default'=>false]);

        Section::firstOrCreate(['code'=>'home'], [
            'name'=>'Home',
            'default_controller' => \App\Http\Controllers\TextSectionController::class,
            'requires_auth' => false,
            'default_title' => 'Home',
        ]);

        Section::firstOrCreate(['code'=>'catalog'], [
            'name'=>'Catalog',
            'default_controller' => \App\Http\Controllers\CatalogController::class,
            'requires_auth' => false,
            'default_title' => 'Catalog',
        ]);
    }
}
