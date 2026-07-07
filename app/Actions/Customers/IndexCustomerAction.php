<?php

namespace App\Actions\Customers;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexCustomerAction
{
    public function __invoke(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', 'active');
        $state = $request->input('state');
        $city = $request->input('city');
        $perPage = (int) $request->input('per_page', 15);

        $matchingIdentificationTypeIds = $this->matchingIdentificationTypeIds($search);

        $query = Customer::query()
            ->withCount('pawns')
            ->when($status === 'deleted', fn (Builder $query) => $query->onlyTrashed())
            ->when($status === 'all', fn (Builder $query) => $query->withTrashed())
            ->when($state, fn (Builder $query) => $query->where('state', $state))
            ->when($city, fn (Builder $query) => $query->where('city', $city))
            ->when($search !== '', function (Builder $query) use ($search, $matchingIdentificationTypeIds) {
                $query->where(function (Builder $builder) use ($search, $matchingIdentificationTypeIds) {
                    $builder
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('rfc', 'like', "%{$search}%")
                        ->orWhere('code_id', 'like', "%{$search}%")
                        ->orWhere('inapam_code', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%");

                    if ($matchingIdentificationTypeIds !== []) {
                        $builder->orWhereIn('type_id', $matchingIdentificationTypeIds);
                    }
                });
            });

        $customers = $query
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Customer $customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'state' => $customer->state,
                'city' => $customer->city,
                'address' => $customer->address,
                'phone' => $customer->phone,
                'mobile' => $customer->mobile,
                'display_phone' => $customer->display_phone,
                'email' => $customer->email,
                'rfc' => $customer->rfc,
                'code_id' => $customer->code_id,
                'type_id' => $customer->type_id,
                'type_label' => $customer->identification_type_label,
                'inapam_code' => $customer->inapam_code,
                'pawns_count' => (int) $customer->pawns_count,
                'created_at' => $customer->created_at?->format('d/m/Y H:i'),
                'updated_at' => $customer->updated_at?->format('d/m/Y H:i'),
                'deleted_at' => $customer->deleted_at?->format('d/m/Y H:i'),
                'is_deleted' => $customer->trashed(),
            ]);

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'summary' => [
                'total' => Customer::query()->withTrashed()->count(),
                'active' => Customer::query()->count(),
                'deleted' => Customer::onlyTrashed()->count(),
                'with_pawns' => Customer::query()->has('pawns')->count(),
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'state' => $state,
                'city' => $city,
                'per_page' => $perPage,
            ],
            'options' => [
                'statuses' => [
                    ['value' => 'active', 'label' => 'Activos'],
                    ['value' => 'deleted', 'label' => 'Eliminados'],
                    ['value' => 'all', 'label' => 'Todos'],
                ],
                'states' => Customer::query()
                    ->whereNotNull('state')
                    ->where('state', '<>', '')
                    ->select('state')
                    ->distinct()
                    ->orderBy('state')
                    ->pluck('state')
                    ->values(),
                'cities' => Customer::query()
                    ->whereNotNull('city')
                    ->where('city', '<>', '')
                    ->select('city')
                    ->distinct()
                    ->orderBy('city')
                    ->pluck('city')
                    ->values(),
            ],
        ]);
    }

    private function matchingIdentificationTypeIds(string $search): array
    {
        if ($search === '') {
            return [];
        }

        return collect(Customer::IDENTIFICATION_TYPES)
            ->filter(fn (string $label) => str_contains(
                mb_strtolower($label),
                mb_strtolower($search)
            ))
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }
}