<?php

namespace App\Actions\Pawns;

use App\Models\Pawn;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PrintPawnAnticipatedDateTicketAction
{
    public function __invoke(Request $request, Pawn $pawn): View|RedirectResponse
    {
        $pawn->load([
            'customer:id,name,state,city,address,phone,mobile,email,rfc,code_id,type_id,inapam_code',
            'office:id,name,serie,company_id,phone,address',
            'office.company:id,name,rfc,phone,email,website,address',
            'items.product:id,code,description,unit',
        ]);

        if (! $this->canUseAnticipatedDate($pawn)) {
            return redirect()
                ->route('pawns.show', $pawn->id)
                ->with('error', 'No se puede imprimir fecha anticipada para este empeño.');
        }

        $date = $this->resolveDate($request);
        $payment = $this->paymentPreview($pawn, $date);

        $company = $pawn->office?->company;
        $customer = $pawn->customer;

        $companyDescription = $this->companyDescription($pawn);

        return view('pawns.prints.ticket-anticipated-date', [
            'pawn' => $pawn,
            'customer' => $customer,
            'company' => $company,
            'companyDescription' => $companyDescription,
            'new' => $date,
            'payment' => $payment,
        ]);
    }

    private function canUseAnticipatedDate(Pawn $pawn): bool
    {
        return $pawn->auction_at === null
            && $pawn->paid_at === null
            && $pawn->canceled_at === null
            && ! $pawn->hasCountersign();
    }

    private function resolveDate(Request $request): Carbon
    {
        $date = trim((string) $request->query('date', now()->toDateString()));

        try {
            return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (Throwable) {
            return now()->startOfDay();
        }
    }

    private function paymentPreview(Pawn $pawn, Carbon $date): array
    {
        $total = (float) $pawn->total;

        $days = max(
            $this->minimumDays($pawn),
            Carbon::parse($pawn->created_at?->toDateString() ?: now()->toDateString())
                ->diffInDays(Carbon::parse($date->toDateString()))
        );

        $dailyRate = (float) $pawn->daily_interest_rate / 100;
        $ivaRate = (float) $pawn->iva_rate;
        $ivaFactor = $ivaRate > 1 ? $ivaRate / 100 : $ivaRate;

        $inapamRate = (float) ($pawn->inapam_rate ?? 0);
        $inapamFactor = $inapamRate > 1 ? $inapamRate / 100 : $inapamRate;

        $paidAmount = (float) $pawn->transactionsPaidAmount()->sum('amount');

        $dailyInterestWithoutIva = round($total * $dailyRate, 2);
        $interestWithoutIva = round($dailyInterestWithoutIva * $days, 2);
        $iva = round($interestWithoutIva * $ivaFactor, 2);

        $interestWithIva = round($interestWithoutIva + $iva, 2);
        $inapamDiscount = $inapamFactor > 0 ? round($interestWithIva * $inapamFactor, 2) : 0;

        $interestToPay = round(max($interestWithIva - $inapamDiscount - $paidAmount, 0), 2);

        return [
            'date' => $date->toDateString(),
            'days_to_pay' => $days,
            'principal' => $total,
            'daily_interest' => $dailyInterestWithoutIva,
            'interest_without_iva' => $interestWithoutIva,
            'iva' => $iva,
            'interest_with_iva' => $interestWithIva,
            'inapam_discount' => $inapamDiscount,
            'paid_amount' => $paidAmount,
            'interest_to_pay' => $interestToPay,
            'amount_to_liquidate' => round($total + $interestToPay, 2),
        ];
    }

    private function minimumDays(Pawn $pawn): int
    {
        $firstProductId = $pawn->items->first()?->product_id;

        if ((int) $firstProductId === 34) {
            return 15;
        }

        return 5;
    }

    private function companyDescription(Pawn $pawn): string
    {
        $company = $pawn->office?->company;
        $office = $pawn->office;

        return collect([
            $company?->name,
            $company?->rfc ? 'RFC: '.$company->rfc : null,
            $office?->name,
            $office?->address ?: $company?->address,
            $office?->phone ?: $company?->phone,
        ])
            ->filter()
            ->join("\n");
    }
}