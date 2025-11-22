<?php

namespace App\Http\Controllers\Api;

use App\Cart\CartManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addProduct(Request $request): JsonResponse
    {
        $data = $this->validateAddOrUpdateRequest($request);

        $sessionCode = $this->resolveSessionCode($request);

        CartManager::addItem('product', (string)$data['item_id'], (int)$data['quantity'], [], null, $sessionCode);

        return response()->json(
            $this->cartResponse($sessionCode, returnWord('Successfully added to cart.', WORDS_PROJECT))
        );
    }

    public function setProductQuantity(Request $request): JsonResponse
    {
        $data = $this->validateAddOrUpdateRequest($request);

        $sessionCode = $this->resolveSessionCode($request);

        CartManager::setItemQuantity('product', (string)$data['item_id'], (int)$data['quantity'], [], null, $sessionCode);

        return response()->json(
            $this->cartResponse($sessionCode, returnWord('Successfully set.', WORDS_PROJECT))
        );
    }

    public function removeItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'min:1'],
        ]);

        $sessionCode = $this->resolveSessionCode($request);

        CartManager::removeItem((int)$validated['id'], null, $sessionCode);

        $items = CartManager::getItems(null, $sessionCode);
        $response = $this->cartResponse($sessionCode, returnWord('Successfully removed.', WORDS_PROJECT));

        if (method_exists($items, 'isEmpty') && $items->isEmpty()) {
            $response['redirect_href'] = sectionHref('cart');
        }

        return response()->json($response);
    }

    protected function validateAddOrUpdateRequest(Request $request): array
    {
        return $request->validate([
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);
    }

    protected function cartResponse(string $sessionCode, string $message): array
    {
        $summary = CartManager::getSummary(null, $sessionCode);

        return [
            'error' => false,
            'summary' => $summary,
            'summary_html' => view('pages.cart.partials.summary', [
                'summary' => $summary,
            ])->render(),
            'cart_dropdown' => view('partials.cart_dropdown', [
                'items' => CartManager::getItems(null, $sessionCode),
                'summary' => $summary,
            ])->render(),
            'msg' => $message,
        ];
    }

    protected function resolveSessionCode(Request $request): string
    {
        $headerCode = (string)$request->header('X-Session-Code', '');

        if ($headerCode !== '') {
            session(['session_code' => $headerCode]);
            return $headerCode;
        }

        return (string)(session('session_code') ?? '');
    }
}
