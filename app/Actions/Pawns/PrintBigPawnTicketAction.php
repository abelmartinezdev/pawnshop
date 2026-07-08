<?php

namespace App\Actions\Pawns;

use App\Models\Pawn;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Throwable;

class PrintBigPawnTicketAction
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
            'customer:id,name,state,city,address,phone,mobile,email,rfc,code_id,type_id,inapam_code',
            'office:id,company_id,name,serie,phone,address,schedule,bank_account,daily_interest_rate,monthly_interest_rate,cash',
            'office.company:id,name,code,rfc,phone,email,website,address,storage_commission,marketing_commission,delayed_payment_commission,replacement_contract_commission',
            'company:id,name,code,rfc,phone,email,website,address,storage_commission,marketing_commission,delayed_payment_commission,replacement_contract_commission',
            'createdBy:id,name,email',
            'cashier:id,name,email',
            'items.product:id,code,description,unit,min_price,max_price,department_id',
        ]);

        $company = $pawn->company ?: $pawn->office?->company;
        $customer = $pawn->customer;
        $office = $pawn->office;

        $principal = round((float) $pawn->total, 2);
        $loanRate = $this->percentDisplay($pawn->loan_rate);
        $estimatedValue = $this->estimatedValue($pawn, $principal, $loanRate);

        $term = max((int) ($pawn->term ?: 30), 1);
        $auctionDays = max((int) ($pawn->auction ?: 0), 0);

        $interestWithoutIva = $this->interestWithoutIvaForDays($pawn, $term);
        $ivaAmount = $this->ivaAmount($pawn, $interestWithoutIva);
        $interestWithIva = round($interestWithoutIva + $ivaAmount, 2);

        $storageCommission = round((float) ($company?->storage_commission ?? 0), 2);
        $marketingCommission = round((float) ($company?->marketing_commission ?? 0), 2);
        $delayedPaymentCommission = round((float) ($company?->delayed_payment_commission ?? 0), 2);
        $replacementContractCommission = round((float) ($company?->replacement_contract_commission ?? 0), 2);

        $commissionsTotal = round(
            $storageCommission
            + $marketingCommission
            + $delayedPaymentCommission
            + $replacementContractCommission,
            2
        );

        $totalToPayAtTerm = round($principal + $interestWithIva + $commissionsTotal, 2);

        return view('pawns.prints.big-ticket', [
            'pawn' => $pawn,
            'customer' => $customer,
            'office' => $office,
            'company' => $company,
            'items' => $pawn->items,
            'folio' => $pawn->formatted_folio,
            'rawFolio' => $this->rawFolio($pawn),
            'date' => $this->formatDate($pawn->created_at, 'd-m-Y'),
            'dateLong' => $this->formatDateLong($pawn->created_at),
            'time' => $this->formatDate(now(), 'H:i:s'),
            'cashier' => $pawn->createdBy?->name ?: $pawn->cashier?->name ?: auth()->user()?->name,

            'principal' => $principal,
            'estimatedValue' => $estimatedValue,
            'loanRate' => $loanRate,
            'term' => $term,
            'auctionDays' => $auctionDays,

            'dailyInterestRate' => (float) $pawn->daily_interest_rate,
            'monthlyInterestRate' => (float) $pawn->monthly_interest_rate,
            'ivaRate' => $this->ivaDisplay($pawn),
            'ivaFactor' => $this->ivaFactor($pawn),

            'interestWithoutIva' => $interestWithoutIva,
            'ivaAmount' => $ivaAmount,
            'interestWithIva' => $interestWithIva,

            'storageCommission' => $storageCommission,
            'marketingCommission' => $marketingCommission,
            'delayedPaymentCommission' => $delayedPaymentCommission,
            'replacementContractCommission' => $replacementContractCommission,
            'commissionsTotal' => $commissionsTotal,
            'totalToPayAtTerm' => $totalToPayAtTerm,

            'catAnnualNoIva' => $this->catAnnualNoIva($pawn),
            'catAnnualWithIva' => $this->catAnnualWithIva($pawn),

            'paymentOptions' => $this->paymentOptions($pawn),
            'dateExpiration' => $this->formatDate($pawn->date_expiration, 'd-m-Y'),
            'dateAuction' => $this->formatDate($pawn->date_auction, 'd-m-Y'),
            'customerAddress' => $this->customerAddress($customer),
        ]);
    }

    private function paymentOptions(Pawn $pawn): array
    {
        $principal = round((float) $pawn->total, 2);
        $term = max((int) ($pawn->term ?: 30), 1);

        return collect(range(1, 3))
            ->map(function (int $number) use ($pawn, $principal, $term) {
                $days = $term * $number;

                $interestWithoutIva = $this->interestWithoutIvaForDays($pawn, $days);
                $iva = $this->ivaAmount($pawn, $interestWithoutIva);
                $interestWithIva = round($interestWithoutIva + $iva, 2);

                return [
                    'number' => $number,
                    'days' => $days,
                    'principal' => $principal,
                    'interest' => $interestWithoutIva,
                    'storage' => 0,
                    'iva' => $iva,
                    'for_refinance' => $interestWithIva,
                    'for_liquidation' => round($principal + $interestWithIva, 2),
                    'date' => $this->formatDate(
                        Carbon::parse($pawn->created_at)->addDays($days),
                        'd-m-Y'
                    ),
                ];
            })
            ->all();
    }

    private function interestWithoutIvaForDays(Pawn $pawn, int $days): float
    {
        $dailyRate = (float) $pawn->daily_interest_rate;

        return round(((float) $pawn->total) * ($dailyRate / 100) * max($days, 1), 2);
    }

    private function ivaAmount(Pawn $pawn, float $amount): float
    {
        return round($amount * $this->ivaFactor($pawn), 2);
    }

    private function ivaFactor(Pawn $pawn): float
    {
        $iva = (float) $pawn->iva_rate;

        if ($iva <= 0) {
            return 0;
        }

        return $iva > 1 ? $iva / 100 : $iva;
    }

    private function ivaDisplay(Pawn $pawn): float
    {
        $iva = (float) $pawn->iva_rate;

        if ($iva <= 0) {
            return 0;
        }

        return $iva > 1 ? $iva : $iva * 100;
    }

    private function percentDisplay(mixed $value): float
    {
        $rate = (float) $value;

        if ($rate <= 0) {
            return 0;
        }

        return $rate > 1 ? $rate : $rate * 100;
    }

    private function estimatedValue(Pawn $pawn, float $principal, float $loanRate): float
    {
        $estimated = (float) $pawn->estimated_value;

        if ($estimated > 0) {
            return round($estimated, 2);
        }

        if ($loanRate > 0) {
            return round($principal / ($loanRate / 100), 2);
        }

        return round((float) ($pawn->loan_value ?: $principal), 2);
    }

    private function catAnnualNoIva(Pawn $pawn): float
    {
        return round(((float) $pawn->daily_interest_rate) * 365, 2);
    }

    private function catAnnualWithIva(Pawn $pawn): float
    {
        return round($this->catAnnualNoIva($pawn) * (1 + $this->ivaFactor($pawn)), 2);
    }

    private function rawFolio(Pawn $pawn): int|string
    {
        try {
            return $pawn->raw_folio ?? $pawn->getRawOriginal('folio') ?? $pawn->folio;
        } catch (Throwable) {
            return $pawn->folio;
        }
    }

    private function customerAddress($customer): string
    {
        if (! $customer) {
            return 'NO CAPTURADA';
        }

        return trim(collect([
            $customer->address,
            $customer->city,
            $customer->state,
        ])->filter()->implode(', ')) ?: 'NO CAPTURADA';
    }

    private function formatDate(mixed $value, string $format): ?string
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->format($format);
        }

        try {
            return Carbon::parse($value)->format($format);
        } catch (Throwable) {
            return (string) $value;
        }
    }

    private function formatDateLong(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)
                ->locale('es')
                ->translatedFormat('j \d\e F \d\e Y');
        } catch (Throwable) {
            return (string) $value;
        }
    }
}