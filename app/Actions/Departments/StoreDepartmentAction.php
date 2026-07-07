<?php

namespace App\Actions\Departments;

use App\Http\Requests\Departments\StoreDepartmentRequest;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;

class StoreDepartmentAction
{
    public function __invoke(StoreDepartmentRequest $request): RedirectResponse
    {
        $department = Department::query()->create($request->validated());

        return redirect()
            ->route('departments.show', $department->id)
            ->with('success', 'Departamento registrado correctamente.');
    }
}