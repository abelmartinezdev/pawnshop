<?php

namespace App\Actions\Pawns;

use App\Models\Pawn;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Throwable;

class PrintCountersignTicketAction
{
    public function __invoke(Pawn $pawn): View
    {
        $officeId = session('office_id') ?: auth()->user()?->office_id;
        $companyId = session('company_id') ?: auth()->user()?->company_id;

        abort_unless($officeId, 404, 'No hay una sucursal activa.');
        abort_unless($companyId, 404, 'No hay una empresa activa.');
        abort_if((int) $pawn->office_id !== (int) $officeId, 403, 'El empeño no pertenece a la sucursal activa.');
        abort_if((int) $pawn->company_id !== (int) $companyId, 403, 'El empeño no pertenece a la empresa activa.');

        $pawn->load([
            'customer:id,name,address,city,state,phone,mobile,email,rfc,code_id,type_id',
            'office:id,name,serie,address,phone',
            'creator:id,name,email',
            'items.product:id,code,description,unit',
        ]);

        $interest = $this->interestToPay($pawn);
        $interestWithoutIva = $this->interestWithoutIva($pawn);
        $iva = round($interest - $interestWithoutIva, 2);
        $payExtra = round((float) request('pay_extra', 0), 2);
        $totalToPay = round($interest + $payExtra, 2);

        return view('pawns.prints.countersign', [
            'pawn' => $pawn,
            'customer' => $pawn->customer,
            'office' => $pawn->office,
            'items' => $pawn->items,
            'folio' => $pawn->formatted_folio,
            'date' => now()->format('d-m-Y'),
            'time' => now()->format('H:i:s'),
            'cashier' => auth()->user()?->name,
            'daysToPay' => $this->daysToPay($pawn),
            'interest' => $interest,
            'interestWithoutIva' => $interestWithoutIva,
            'iva' => $iva,
            'payExtra' => $payExtra,
            'totalToPay' => $totalToPay,
            'newPrincipal' => max(round((float) $pawn->total - $payExtra, 2), 0),
        ]);
    }

    private function interestToPay(Pawn $pawn): float
    {
        try {
            return round((float) $pawn->getInterest2payLess1day(), 2);
        } catch (Throwable) {
            $days = $this->daysToPay($pawn);
            $dailyRate = (float) $pawn->daily_interest_rate / 100;
            $interest = (float) $pawn->total * $dailyRate * $days;

            $iva = (float) $pawn->iva_rate;
            $ivaFactor = $iva > 1 ? $iva / 100 : $iva;

            $interest += $interest * $ivaFactor;

            if ($pawn->inapam_rate) {
                $interest -= $interest * (float) $pawn->inapam_rate;
            }

            return round(max($interest, 0), 2);
        }
    }

    private function interestWithoutIva(Pawn $pawn): float
    {
        try {
            return round((float) $pawn->getInterest2payLess1day(false), 2);
        } catch (Throwable) {
            $days = $this->daysToPay($pawn);
            $dailyRate = (float) $pawn->daily_interest_rate / 100;

            return round(max((float) $pawn->total * $dailyRate * $days, 0), 2);
        }
    }

    private function daysToPay(Pawn $pawn): int
    {
        try {
            return max((int) $pawn->days2payminus1, 1);
        } catch (Throwable) {
            return max(Carbon::parse($pawn->created_at)->diffInDays(now()), 1);
        }
    }
}