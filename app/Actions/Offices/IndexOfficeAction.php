<?php

namespace App\Actions\Offices;

use App\Models\Office;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexOfficeAction
{
    public function __invoke(Request $request): Response
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));
        $status = (string) $request->input('status', 'active');
        $companyId = $request->input('company_id');
        $perPage = (int) $request->input('per_page', $request->input('perPage', 10));

        $query = Office::query()
            ->with('company:id,name,code')
            ->withCount(['transactions', 'pawns'])
            ->when($status === 'trashed', fn ($query) => $query->onlyTrashed())
            ->when($status === 'all', fn ($query) => $query->withTrashed())
            ->when($status === 'active', fn ($query) => $query)
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('serie', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhereHas('company', function ($companyQuery) use ($search) {
                            $companyQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('name');

        $companies = \App\Models\Company::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn ($company) => [
                'id' => $company->id,
                'name' => $company->name,
                'code' => $company->code,
            ]);

        return Inertia::render('Offices/Index', [
            'offices' => $query
                ->paginate($perPage)
                ->withQueryString()
                ->through(fn (Office $office) => [
                    'id' => $office->id,
                    'company_id' => $office->company_id,
                    'name' => $office->name,
                    'display_name' => $office->display_name,
                    'code' => $office->code,
                    'serie' => $office->serie,
                    'phone' => $office->phone,
                    'address' => $office->address,
                    'schedule' => $office->schedule,
                    'bank_account' => $office->bank_account,
                    'daily_interest_rate' => (float) $office->daily_interest_rate,
                    'monthly_interest_rate' => (float) $office->monthly_interest_rate,
                    'cash' => (float) $office->cash,
                    'is_deleted' => $office->trashed(),
                    'status_label' => $office->status_label,
                    'transactions_count' => (int) $office->transactions_count,
                    'pawns_count' => (int) $office->pawns_count,
                    'created_at' => optional($office->created_at)->format('d/m/Y H:i'),
                    'updated_at' => optional($office->updated_at)->format('d/m/Y H:i'),
                    'deleted_at' => optional($office->deleted_at)->format('d/m/Y H:i'),
                    'company' => $office->company ? [
                        'id' => $office->company->id,
                        'name' => $office->company->name,
                        'code' => $office->company->code,
                    ] : null,
                ]),

            'summary' => [
                'total' => Office::withTrashed()->count(),
                'active' => Office::query()->count(),
                'trashed' => Office::onlyTrashed()->count(),
                'cash_total' => (float) Office::query()->sum('cash'),
                'negative_cash' => Office::query()->where('cash', '<', 0)->count(),
            ],

            'filters' => [
                'search' => $search,
                'status' => $status,
                'company_id' => $companyId ? (int) $companyId : null,
                'per_page' => $perPage,
            ],

            'options' => [
                'statuses' => [
                    ['value' => 'active', 'label' => 'Activas'],
                    ['value' => 'trashed', 'label' => 'Archivadas'],
                    ['value' => 'all', 'label' => 'Todas'],
                ],
                'companies' => $companies,
            ],
        ]);
    }
}