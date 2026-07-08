<?php

namespace App\Http\Controllers\Companies;

use App\Actions\Companies\CreateCompanyAction;
use App\Actions\Companies\DeleteCompanyAction;
use App\Actions\Companies\EditCompanyAction;
use App\Actions\Companies\IndexCompanyAction;
use App\Actions\Companies\RestoreCompanyAction;
use App\Actions\Companies\ShowCompanyAction;
use App\Actions\Companies\StoreCompanyAction;
use App\Actions\Companies\UpdateCompanyAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Companies\StoreCompanyRequest;
use App\Http\Requests\Companies\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class CompanyController extends Controller
{
    public function index(Request $request, IndexCompanyAction $action): Response
    {
        return $action($request);
    }

    public function create(CreateCompanyAction $action): Response
    {
        return $action();
    }

    public function store(StoreCompanyRequest $request, StoreCompanyAction $action): RedirectResponse
    {
        return $action($request);
    }

    public function show(Company $company, ShowCompanyAction $action): Response
    {
        return $action($company);
    }

    public function edit(Company $company, EditCompanyAction $action): Response
    {
        return $action($company);
    }

    public function update(
        UpdateCompanyRequest $request,
        Company $company,
        UpdateCompanyAction $action
    ): RedirectResponse {
        return $action($request, $company);
    }

    public function destroy(Company $company, DeleteCompanyAction $action): RedirectResponse
    {
        return $action($company);
    }

    public function restore(int $id, RestoreCompanyAction $action): RedirectResponse
    {
        return $action($id);
    }
}