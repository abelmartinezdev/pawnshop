<?php

namespace App\Actions\Products;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;

class RestoreProductAction
{
    public function __invoke(int $id): RedirectResponse
    {
        $product = Product::query()
            ->onlyTrashed()
            ->findOrFail($id);

        $product->restore();

        return redirect()
            ->route('products.show', $product->id)
            ->with('success', 'Producto restaurado correctamente.');
    }
}