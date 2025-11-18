<?php

namespace App\Http\Controllers;

use App\Cart\CartManager;
use App\Support\PageContext;
use Illuminate\Http\Request;

class CartSectionController extends Controller
{
    public function handle(Request $request, PageContext $context)
    {
        $sessionCode = $context->sessionCode();

        CartManager::recalculateDiscounts(null, $sessionCode);

        $items = CartManager::getItems(null, $sessionCode);
        $summary = CartManager::getSummary(null, $sessionCode);

        $context->breadcrumbs([
            ['title' => $context->section()->name, 'url' => url()->current()],
        ]);

        if (!$context->meta('title')) {
            $context->meta('title', $context->section()->name);
        }

        return view('sections.cart.index', [
            'page' => $context,
            'items' => $items,
            'summary' => $summary,
        ]);
    }
}
