<?php

namespace App\Actions\Products;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;

class DestroyProductAction
{
    public function __invoke(int $id): RedirectResponse
    {
        $product = Product::query()
            ->withCount('pawnItems')
            ->findOrFail($id);

        if ($product->pawn_items_count > 0) {
            return redirect()
                ->route('products.show', $product->id)
                ->with('error', 'No puedes eliminar este producto porque ya está relacionado con empeños.');
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Producto eliminado correctamente.');
    }
}