<?php

namespace App\Actions\Products;

use App\Http\Requests\Products\StoreProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;

class StoreProductAction
{
    public function __invoke(StoreProductRequest $request): RedirectResponse
    {
        $product = Product::query()->create($request->validated());

        return redirect()
            ->route('products.show', $product->id)
            ->with('success', 'Producto registrado correctamente.');
    }
}