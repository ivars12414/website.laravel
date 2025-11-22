<?php

namespace Database\Seeders;

use App\Http\Controllers\TextSectionController;
use App\Models\Content;
use App\Models\Language;
use App\Models\Section;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $languages = Language::all();

        foreach ($languages as $language) {
            $section = Section::firstOrCreate(
                ['label' => 'news', 'lang_id' => $language->id],
                [
                    'code' => Str::slug($language->code === 'lv' ? 'Ziņas' : 'News'),
                    'name' => $language->code === 'lv' ? 'Ziņas' : 'News',
                    'default_controller' => '',
                    'requires_auth' => false,
                    'default_title' => $language->code === 'lv' ? 'Jaunākās ziņas' : 'Latest news',
                    'default_h1' => $language->code === 'lv' ? 'Jaunākās ziņas' : 'Latest news',
                    'position' => Section::POSITION_HEADER,
                    'status' => 1,
                ]
            );

            $faker = Factory::create($language->code === 'lv' ? 'lv_LV' : 'en_US');

            for ($i = 1; $i <= 6; $i++) {
                $title = $faker->sentence(6);
                $slug = Str::slug($title) ?: 'news-' . $i;

                $hash = md5('news-' . $i);
                $publishedAt = Carbon::now()->subDays($i);

                Content::updateOrCreate(
                    ['hash' => $hash, 'lang_id' => $language->id],
                    [
                        'section_hash1' => $section->hash,
                        'slug' => $slug,
                        'title' => $title,
                        'header' => $faker->sentence(12),
                        'content' => $this->buildContent($faker),
                        'img' => 'https://picsum.photos/seed/' . $slug . '/600/400',
                        'tm_unix' => $publishedAt->timestamp,
                        'show_dt' => 1,
                        'link' => null,
                    ]
                );
            }
        }
    }

    private function buildContent($faker): string
    {
        $paragraphs = $faker->paragraphs(4);

        return collect($paragraphs)
            ->map(fn($text) => '<p>' . $text . '</p>')
            ->implode("\n\n");
    }
}
