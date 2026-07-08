<?php

namespace App\Actions\Companies;

use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexCompanyAction
{
    public function __invoke(Request $request): Response
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));
        $status = (string) $request->input('status', 'active');
        $perPage = (int) $request->input('per_page', $request->input('perPage', 10));

        $query = Company::query()
            ->withCount('offices')
            ->when($status === 'trashed', fn ($query) => $query->onlyTrashed())
            ->when($status === 'all', fn ($query) => $query->withTrashed())
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('rfc', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('name');

        return Inertia::render('Companies/Index', [
            'companies' => $query
                ->paginate($perPage)
                ->withQueryString()
                ->through(fn (Company $company) => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'code' => $company->code,
                    'rfc' => $company->rfc,
                    'phone' => $company->phone,
                    'email' => $company->email,
                    'address' => $company->address,
                    'website' => $company->website,
                    'is_active' => (bool) $company->is_active,
                    'is_deleted' => $company->trashed(),
                    'status_label' => $company->status_label,
                    'offices_count' => (int) $company->offices_count,
                    'created_at' => optional($company->created_at)->format('d/m/Y H:i'),
                    'updated_at' => optional($company->updated_at)->format('d/m/Y H:i'),
                    'deleted_at' => optional($company->deleted_at)->format('d/m/Y H:i'),
                ]),

            'summary' => [
                'total' => Company::withTrashed()->count(),
                'active' => Company::query()->where('is_active', true)->count(),
                'inactive' => Company::query()->where('is_active', false)->count(),
                'trashed' => Company::onlyTrashed()->count(),
                'offices' => Company::query()->withCount('offices')->get()->sum('offices_count'),
            ],

            'filters' => [
                'search' => $search,
                'status' => $status,
                'per_page' => $perPage,
            ],

            'options' => [
                'statuses' => [
                    ['value' => 'active', 'label' => 'Activas'],
                    ['value' => 'inactive', 'label' => 'Inactivas'],
                    ['value' => 'trashed', 'label' => 'Archivadas'],
                    ['value' => 'all', 'label' => 'Todas'],
                ],
            ],
        ]);
    }
}