<?php

namespace App\Actions\Departments;

use App\Models\Department;
use Illuminate\Http\RedirectResponse;

class RestoreDepartmentAction
{
    public function __invoke(int $id): RedirectResponse
    {
        $department = Department::query()
            ->onlyTrashed()
            ->findOrFail($id);

        $department->restore();

        return redirect()
            ->route('departments.show', $department->id)
            ->with('success', 'Departamento restaurado correctamente.');
    }
}