<?php

namespace App\Actions\Closures;

use App\Models\Closure as CashClosure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexClosureAction
{
    public function __invoke(Request $request): Response
    {
        $officeId = session('office_id') ?: $request->user()?->office_id;

        abort_unless($officeId, 404, 'No hay una sucursal activa.');

        $search = trim((string) $request->input('search', ''));
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $perPage = (int) $request->input('per_page', 15);

        $closures = CashClosure::query()
            ->with(['user:id,name,email', 'office:id,name'])
            ->where('office_id', $officeId)
            ->when($dateFrom, fn (Builder $query) => $query->whereDate('closed_at', '>=', $dateFrom))
            ->when($dateTo, fn (Builder $query) => $query->whereDate('closed_at', '<=', $dateTo))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder
                        ->where('id', $search)
                        ->orWhere('comments', 'like', "%{$search}%")
                        ->orWhere('expected_cash', 'like', "%{$search}%")
                        ->orWhere('counted_cash', 'like', "%{$search}%")
                        ->orWhere('difference', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('closed_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (CashClosure $closure) => [
                'id' => $closure->id,
                'period_start_at' => $closure->period_start_at?->format('d/m/Y H:i'),
                'period_end_at' => $closure->period_end_at?->format('d/m/Y H:i'),
                'closed_at' => $closure->closed_at?->format('d/m/Y H:i'),
                'opening_cash' => (float) $closure->opening_cash,
                'cash_in' => (float) $closure->cash_in,
                'cash_out' => (float) $closure->cash_out,
                'expected_cash' => (float) $closure->expected_cash,
                'counted_cash' => (float) $closure->counted_cash,
                'difference' => (float) $closure->difference,
                'total_transactions' => (int) $closure->total_transactions,
                'comments' => $closure->comments,
                'user' => $closure->user ? [
                    'id' => $closure->user->id,
                    'name' => $closure->user->name,
                ] : null,
            ]);

        return Inertia::render('Closures/Index', [
            'closures' => $closures,
            'filters' => [
                'search' => $search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'per_page' => $perPage,
            ],
        ]);
    }
}