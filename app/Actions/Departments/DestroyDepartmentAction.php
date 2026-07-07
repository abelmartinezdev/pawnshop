<?php

namespace App\Actions\Departments;

use App\Models\Department;
use Illuminate\Http\RedirectResponse;

class DestroyDepartmentAction
{
    public function __invoke(int $id): RedirectResponse
    {
        $department = Department::query()
            ->withCount('products')
            ->findOrFail($id);

        if ($department->products_count > 0) {
            return redirect()
                ->route('departments.show', $department->id)
                ->with('error', 'No puedes eliminar este departamento porque tiene productos relacionados.');
        }

        $department->delete();

        return redirect()
            ->route('departments.index')
            ->with('success', 'Departamento eliminado correctamente.');
    }
}