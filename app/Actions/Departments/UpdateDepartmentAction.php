<?php

namespace App\Actions\Departments;

use App\Http\Requests\Departments\UpdateDepartmentRequest;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;

class UpdateDepartmentAction
{
    public function __invoke(int $id, UpdateDepartmentRequest $request): RedirectResponse
    {
        $department = Department::query()->findOrFail($id);

        $department->update($request->validated());

        return redirect()
            ->route('departments.show', $department->id)
            ->with('success', 'Departamento actualizado correctamente.');
    }
}