<?php

namespace App\Actions\Products;

use App\Http\Requests\Products\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;

class UpdateProductAction
{
    public function __invoke(int $id, UpdateProductRequest $request): RedirectResponse
    {
        $product = Product::query()->findOrFail($id);

        $product->update($request->validated());

        return redirect()
            ->route('products.show', $product->id)
            ->with('success', 'Producto actualizado correctamente.');
    }
}