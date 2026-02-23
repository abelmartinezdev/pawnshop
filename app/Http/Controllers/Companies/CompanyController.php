<?php

namespace App\Http\Controllers\Companies;

use App\Actions\Companies\StoreCompanyAction;
use App\Actions\Companies\UpdateCompanyAction;
use App\Actions\Companies\DeleteCompanyAction;
use App\Actions\Companies\RestoreCompanyAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Companies\StoreCompanyRequest;
use App\Http\Requests\Companies\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $status = (string) $request->get('status', 'active'); // active|all|trashed
        $perPage = (int) ($request->get('perPage', 10));

        $query = Company::query()
            ->when($status === 'trashed', fn ($x) => $x->onlyTrashed())
            ->when($status === 'all', fn ($x) => $x->withTrashed())
            ->when($q !== '', function ($x) use ($q) {
                $x->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('code', 'like', "%{$q}%");
                });
            })
            ->orderBy('name');

        $companies = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Companies/Index', [
            'companies' => $companies,
            'filters' => [
                'q' => $q,
                'status' => $status,
                'perPage' => $perPage,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Companies/Create');
    }

    public function store(StoreCompanyRequest $request, StoreCompanyAction $action)
    {
        $action->execute($request->validated());

        return redirect()->route('companies.index')
            ->with('flash', ['type' => 'success', 'message' => 'Empresa creada.']);
    }

    public function edit(Company $company)
    {
        return Inertia::render('Companies/Edit', [
            'company' => $company,
        ]);
    }

    public function update(UpdateCompanyRequest $request, Company $company, UpdateCompanyAction $action)
    {
        $action->execute($company, $request->validated());

        return redirect()->route('companies.index')
            ->with('flash', ['type' => 'success', 'message' => 'Empresa actualizada.']);
    }

    public function destroy(Company $company, DeleteCompanyAction $action)
    {
        $action->execute($company);

        return redirect()->route('companies.index')
            ->with('flash', ['type' => 'success', 'message' => 'Empresa archivada.']);
    }

    public function restore(int $id, RestoreCompanyAction $action)
    {
        $action->execute($id);

        return redirect()->route('companies.index')
            ->with('flash', ['type' => 'success', 'message' => 'Empresa restaurada.']);
    }
}