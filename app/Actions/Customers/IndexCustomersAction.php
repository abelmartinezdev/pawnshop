<?php

namespace App\Actions\Customers;

use App\Models\Customer;
use Illuminate\Http\Request;

class IndexCustomersAction
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $status = (string) $request->get('status', 'active'); // active|all|trashed
        $perPage = (int) ($request->get('perPage', 10));

        $query = Customer::query()
            ->select('id', 'name', 'rfc', 'phone', 'mobile', 'email', 'inapam_code', 'deleted_at', 'created_at')
            ->when($status === 'trashed', fn ($x) => $x->onlyTrashed())
            ->when($status === 'all', fn ($x) => $x->withTrashed())
            ->when($q !== '', function ($x) use ($q) {
                $x->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('rfc', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%")
                      ->orWhere('mobile', 'like', "%{$q}%");
                });
            })
            ->orderBy('name');

        $customers = $query->paginate($perPage)->withQueryString();

        return inertia('Customers/Index', [
            'customers' => $customers,
            'filters' => [
                'q' => $q,
                'status' => $status,
                'perPage' => $perPage,
            ],
        ]);
    }
}