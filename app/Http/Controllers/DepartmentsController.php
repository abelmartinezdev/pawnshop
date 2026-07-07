<?php

namespace App\Http\Controllers;

use App\Actions\Departments\CreateDepartmentAction;
use App\Actions\Departments\DestroyDepartmentAction;
use App\Actions\Departments\EditDepartmentAction;
use App\Actions\Departments\IndexDepartmentAction;
use App\Actions\Departments\RestoreDepartmentAction;
use App\Actions\Departments\ShowDepartmentAction;
use App\Actions\Departments\StoreDepartmentAction;
use App\Actions\Departments\UpdateDepartmentAction;
use App\Http\Requests\Departments\StoreDepartmentRequest;
use App\Http\Requests\Departments\UpdateDepartmentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class DepartmentsController extends Controller
{
    public function index(Request $request, IndexDepartmentAction $action): Response
    {
        return $action($request);
    }

    public function create(CreateDepartmentAction $action): Response
    {
        return $action();
    }

    public function store(StoreDepartmentRequest $request, StoreDepartmentAction $action): RedirectResponse
    {
        return $action($request);
    }

    public function show(int $department, ShowDepartmentAction $action): Response
    {
        return $action($department);
    }

    public function edit(int $department, EditDepartmentAction $action): Response
    {
        return $action($department);
    }

    public function update(
        int $department,
        UpdateDepartmentRequest $request,
        UpdateDepartmentAction $action
    ): RedirectResponse {
        return $action($department, $request);
    }

    public function destroy(int $department, DestroyDepartmentAction $action): RedirectResponse
    {
        return $action($department);
    }

    public function restore(int $id, RestoreDepartmentAction $action): RedirectResponse
    {
        return $action($id);
    }
}