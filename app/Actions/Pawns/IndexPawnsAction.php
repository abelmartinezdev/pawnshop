<?php

namespace App\Actions\Pawns;

use App\Models\Pawn;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexPawnsAction
{
    public function __invoke(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $pawns = Pawn::query()
            ->with(['office:id,serie', 'customer:id,name'])
            ->when($search !== '', fn ($q) => $q->where('folio', 'like', "%{$search}%"))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Pawns/Index', [
            'pawns' => $pawns,
            'filters' => ['search' => $search],
        ]);
    }
}