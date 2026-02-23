<?php

namespace App\Actions\Closures;

use App\Modules\Closures\Closure;
use App\Modules\Companies\Company;
use App\Modules\Offices\Office;
use App\Modules\Users\User;
use Inertia\Inertia;
use Jenssegers\Date\Date;

class TicketClosureAction
{
    public function __invoke(int $id)
    {
        $closure = Closure::findOrFail($id);
        $office  = Office::findOrFail($closure->office_id);
        $company = Company::findOrFail($closure->company_id);
        $date    = Date::parse($closure->created_at)->format('d/m/Y H:i');
        $user    = User::findOrFail($closure->user_id);

        return Inertia::render('Closures/Print', compact('closure', 'office', 'company', 'date', 'user'));
    }
}