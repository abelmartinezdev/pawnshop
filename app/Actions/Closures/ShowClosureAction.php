<?php

namespace App\Actions\Closures;

use App\Modules\Closures\Closure;
use App\Modules\Companies\Company;
use App\Modules\Offices\Office;
use Inertia\Inertia;

class ShowClosureAction
{
    public function __invoke(int $id)
    {
        $closure = Closure::findOrFail($id);
        $office  = Office::findOrFail($closure->office_id);
        $company = Company::findOrFail($closure->company_id);

        return Inertia::render('Closures/Show', compact('closure', 'office', 'company'));
    }
}