<?php

namespace App\Actions\Companies;

use App\Models\Company;
use App\Models\Transaction;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class ShowCompanyAction
{
    public function __invoke(Company $company): Response
    {
        $company->load([
            'offices' => fn ($query) => $query->orderBy('name'),
        ]);

        return Inertia::render('Companies/Show', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'code' => $company->code,
                'rfc' => $company->rfc,
                'phone' => $company->phone,
                'email' => $company->email,
                'address' => $company->address,
                'website' => $company->website,
                'is_active' => (bool) $company->is_active,
                'is_deleted' => $company->trashed(),
                'status_label' => $company->status_label,

                'storage_commission' => (float) $company->storage_commission,
                'marketing_commission' => (float) $company->marketing_commission,
                'delayed_payment_commission' => (float) $company->delayed_payment_commission,
                'replacement_contract_commission' => (float) $company->replacement_contract_commission,

                'created_at' => $this->formatDate($company->created_at),
                'updated_at' => $this->formatDate($company->updated_at),
                'deleted_at' => $this->formatDate($company->deleted_at),

                'offices' => $company->offices
                    ->map(function ($office) {
                        $lastTransaction = Transaction::query()
                            ->where('office_id', $office->id)
                            ->latest('id')
                            ->first();

                        return [
                            'id' => $office->id,
                            'name' => $office->name,
                            'serie' => $office->serie,
                            'phone' => $office->phone,
                            'address' => $office->address,
                            'schedule' => $office->schedule,
                            'bank_account' => $office->bank_account,
                            'daily_interest_rate' => (float) $office->daily_interest_rate,
                            'monthly_interest_rate' => (float) $office->monthly_interest_rate,
                            'cash' => (float) $office->cash,
                            'created_at' => $this->formatDate($office->created_at),
                            'updated_at' => $this->formatDate($office->updated_at),
                            'show_url' => Route::has('offices.show')
                                ? route('offices.show', $office->id)
                                : null,
                            'edit_url' => Route::has('offices.edit')
                                ? route('offices.edit', $office->id)
                                : null,
                            'last_transaction' => $lastTransaction ? [
                                'id' => $lastTransaction->id,
                                'type' => $lastTransaction->type,
                                'amount' => (float) $lastTransaction->amount,
                                'balance' => (float) $lastTransaction->balance,
                                'created_at' => $this->formatDate($lastTransaction->created_at),
                            ] : null,
                        ];
                    })
                    ->values(),
            ],

            'summary' => [
                'offices' => $company->offices->count(),
                'total_cash' => (float) $company->offices->sum('cash'),
                'negative_offices' => $company->offices->filter(fn ($office) => (float) $office->cash < 0)->count(),
                'active_offices' => $company->offices->count(),
            ],

            'urls' => [
                'index' => route('companies.index'),
                'edit' => route('companies.edit', $company->id),
                'destroy' => route('companies.destroy', $company->id),
                'restore' => route('companies.restore', $company->id),
                'office_create' => Route::has('offices.create')
                    ? route('offices.create', ['company' => $company->id])
                    : null,
            ],
        ]);
    }

    private function formatDate(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('d/m/Y H:i');
        }

        return (string) $value;
    }
}