<?php

namespace App\Http\Controllers\Offices;

use App\Http\Controllers\Controller;
use App\Models\Office;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class OfficeController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $status = (string) $request->input('status', 'active');

        $query = $this->officeQuery();

        if ($status === 'deleted' && $this->usesSoftDeletes()) {
            $query->onlyTrashed();
        } elseif ($status === 'all' && $this->usesSoftDeletes()) {
            $query->withTrashed();
        }

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search) {
                if ($this->hasColumn('name')) {
                    $builder->orWhere('name', 'like', "%{$search}%");
                }

                if ($this->hasColumn('nombre')) {
                    $builder->orWhere('nombre', 'like', "%{$search}%");
                }

                if ($this->hasColumn('address')) {
                    $builder->orWhere('address', 'like', "%{$search}%");
                }

                if ($this->hasColumn('direccion')) {
                    $builder->orWhere('direccion', 'like', "%{$search}%");
                }

                if ($this->hasColumn('phone')) {
                    $builder->orWhere('phone', 'like', "%{$search}%");
                }

                if ($this->hasColumn('telefono')) {
                    $builder->orWhere('telefono', 'like', "%{$search}%");
                }
            });
        }

        if ($this->hasColumn('company_id') && session('company_id')) {
            $query->where('company_id', session('company_id'));
        }

        $offices = $query
            ->latest('id')
            ->paginate((int) $request->input('per_page', 15))
            ->withQueryString();

        return Inertia::render('Offices/Index', [
            'offices' => $offices,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'per_page' => (int) $request->input('per_page', 15),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Offices/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatedPayload($request);

        Office::query()->create($payload);

        return redirect()
            ->route('offices.index')
            ->with('success', 'Sucursal creada correctamente.');
    }

    public function edit(Office $office): Response
    {
        $this->authorizeOffice($office);

        return Inertia::render('Offices/Edit', [
            'office' => $office,
        ]);
    }

    public function update(Request $request, Office $office): RedirectResponse
    {
        $this->authorizeOffice($office);

        $payload = $this->validatedPayload($request);

        $office->update($payload);

        return redirect()
            ->route('offices.index')
            ->with('success', 'Sucursal actualizada correctamente.');
    }

    public function destroy(Office $office): RedirectResponse
    {
        $this->authorizeOffice($office);

        $office->delete();

        return redirect()
            ->route('offices.index')
            ->with('success', 'Sucursal eliminada correctamente.');
    }

    public function restore(int|string $id): RedirectResponse
    {
        abort_unless($this->usesSoftDeletes(), 404);

        $office = Office::withTrashed()->findOrFail($id);

        $this->authorizeOffice($office);

        $office->restore();

        return redirect()
            ->route('offices.index')
            ->with('success', 'Sucursal restaurada correctamente.');
    }

    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'nombre' => ['nullable', 'string', 'max:255'],

            'address' => ['nullable', 'string', 'max:500'],
            'direccion' => ['nullable', 'string', 'max:500'],

            'phone' => ['nullable', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:50'],

            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $name = trim((string) ($validated['name'] ?? $validated['nombre'] ?? ''));

        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => 'El nombre de la sucursal es obligatorio.',
            ]);
        }

        $payload = [];

        if ($this->hasColumn('name')) {
            $payload['name'] = $name;
        } elseif ($this->hasColumn('nombre')) {
            $payload['nombre'] = $name;
        }

        $address = $validated['address'] ?? $validated['direccion'] ?? null;

        if ($this->hasColumn('address')) {
            $payload['address'] = $address;
        } elseif ($this->hasColumn('direccion')) {
            $payload['direccion'] = $address;
        }

        $phone = $validated['phone'] ?? $validated['telefono'] ?? null;

        if ($this->hasColumn('phone')) {
            $payload['phone'] = $phone;
        } elseif ($this->hasColumn('telefono')) {
            $payload['telefono'] = $phone;
        }

        if ($this->hasColumn('email')) {
            $payload['email'] = $validated['email'] ?? null;
        }

        if ($this->hasColumn('is_active')) {
            $payload['is_active'] = (bool) ($validated['is_active'] ?? true);
        }

        if ($this->hasColumn('company_id') && session('company_id')) {
            $payload['company_id'] = session('company_id');
        }

        return $payload;
    }

    private function authorizeOffice(Office $office): void
    {
        if ($this->hasColumn('company_id') && session('company_id')) {
            abort_if((int) $office->company_id !== (int) session('company_id'), 403);
        }
    }

    private function officeQuery(): Builder
    {
        return Office::query();
    }

    private function hasColumn(string $column): bool
    {
        return Schema::hasColumn((new Office())->getTable(), $column);
    }

    private function usesSoftDeletes(): bool
    {
        return in_array(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            class_uses_recursive(Office::class),
            true
        );
    }
}