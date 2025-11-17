<?php

use App\Models\Category;
use App\Models\ItemImage;

function returnCategoryLink($category): string
{

    $categoryPath = '';

    while (true) {

        $categoryPath = '/' . $category->slug . $categoryPath;

        if (!empty($category->parent_id)) {
            $category = Category::where('id', $category->parent_id);
        } else {
            // Родительская категория не найдена
            break;
        }

    }

    return sectionHref('catalog') . $categoryPath;
}

function returnItemLink($data): string
{
    return returnCategoryLink($data->mainCategory) . '/' . $data->slug;
}

function checkCategoryExisting(string $link): array
{

    $currentCategory = Category::fromLink($link);

    return ['error' => !$currentCategory || count(array_filter(explode('/', $link))) !== count($currentCategory->tree), 'current_category' => $currentCategory];
}

function getItemPhotos($itemHash): ?array
{
    return ItemImage::where('item_hash', $itemHash)
        ->where('status', 1)
        ->orderBy('ord')
        ->orderBy('id')
        ->get();
}

function buildCatUrlByCategoryId($id)
{

    $link = '';

    $ic = 1;
    $cats[$ic] = getCatById($id);
    if (!empty($cats[$ic]) && $cats[$ic]['status'] != '1') {
        return '';
    }

    for ($i = 2; $i <= 4; $i++) {
        $ic = $i;
        $cats[$ic] = getCatById($cats[$ic - 1]['parent_id']);
        if (!empty($cats[$ic]) && $cats[$ic]['status'] != '1') {
            return '';
            break;
        }
    }

    krsort($cats);

    foreach ($cats as $index => $data) {
        if (!empty($data)) {
            $tmp = $data['code'] . '/';
        }
        $link = $link . $tmp;
    }

    return $link;
}

function getCatById($id, $lang = 0)
{
    return Category::where('id', (int)$id);
}
