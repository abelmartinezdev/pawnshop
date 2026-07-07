<?php

namespace App\Actions\Products;

use App\Models\PawnItem;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class ShowProductAction
{
    public function __invoke(int $id): Response
    {
        $product = Product::query()
            ->withTrashed()
            ->with(['department'])
            ->withCount('pawnItems')
            ->findOrFail($id);

        $recentItems = PawnItem::query()
            ->with([
                'pawn:id,folio,customer_id,office_id,total,created_at,paid_at,canceled_at,date_expiration',
                'pawn.customer:id,name,phone,mobile',
                'pawn.office:id,name,serie',
            ])
            ->where('product_id', $product->id)
            ->latest('id')
            ->take(10)
            ->get()
            ->map(fn (PawnItem $item) => [
                'id' => $item->id,
                'quantity' => (float) $item->quantity,
                'description' => $item->description,
                'value' => (float) $item->value,
                'created_at' => $item->created_at?->format('d/m/Y H:i'),
                'pawn' => $item->pawn ? [
                    'id' => $item->pawn->id,
                    'folio' => $item->pawn->formatted_folio,
                    'total' => (float) $item->pawn->total,
                    'status' => $item->pawn->status,
                    'status_label' => $item->pawn->status_label,
                    'date_expiration' => $item->pawn->date_expiration?->format('d/m/Y'),
                    'created_at' => $item->pawn->created_at?->format('d/m/Y H:i'),
                    'customer' => $item->pawn->customer ? [
                        'id' => $item->pawn->customer->id,
                        'name' => $item->pawn->customer->name,
                        'phone' => $item->pawn->customer->display_phone,
                    ] : null,
                ] : null,
            ])
            ->values();

        return Inertia::render('Products/Show', [
            'product' => [
                'id' => $product->id,
                'department_id' => $product->department_id,
                'code' => $product->code,
                'description' => $product->description,
                'unit' => $product->unit,
                'min_price' => (float) $product->min_price,
                'max_price' => (float) $product->max_price,
                'price_range' => $product->price_range,
                'is_active' => (bool) $product->is_active,
                'pawn_items_count' => (int) $product->pawn_items_count,
                'created_at' => $product->created_at?->format('d/m/Y H:i'),
                'updated_at' => $product->updated_at?->format('d/m/Y H:i'),
                'deleted_at' => $product->deleted_at?->format('d/m/Y H:i'),
                'is_deleted' => $product->trashed(),
                'department' => $product->department ? [
                    'id' => $product->department->id,
                    'code' => $product->department->code,
                    'description' => $product->department->description,
                    'display_name' => $product->department->display_name,
                    'loan_rate' => (float) $product->department->loan_rate,
                    'daily_interest_rate' => (float) $product->department->daily_interest_rate,
                    'monthly_interest_rate' => (float) $product->department->monthly_interest_rate,
                    'term' => $product->department->term,
                    'auction' => $product->department->auction,
                    'color' => $product->department->color,
                ] : null,
                'recent_items' => $recentItems,
            ],
        ]);
    }
}