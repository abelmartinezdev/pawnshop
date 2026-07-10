<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Fecha anticipada</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            line-height: 1.28;
            text-transform: uppercase;
        }

        @page {
            margin: 0;
            size: 80mm auto;
        }

        .ticket {
            width: 78mm;
            max-width: 78mm;
            margin: 0 auto;
            padding: 8px 7px 10px;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .muted {
            color: #4b5563;
        }

        .strong {
            font-weight: 800;
        }

        .header {
            padding-bottom: 7px;
            border-bottom: 1px dashed #9ca3af;
        }

        .company {
            font-size: 10.5px;
            font-weight: 700;
            line-height: 1.3;
            white-space: pre-line;
        }

        .title-box {
            margin-top: 7px;
            padding: 6px 5px;
            border: 1px solid #111827;
            border-radius: 4px;
            background: #f3f4f6;
        }

        .title {
            margin: 0;
            font-size: 14px;
            font-weight: 900;
            letter-spacing: 0.5px;
        }

        .subtitle {
            margin-top: 2px;
            font-size: 9.5px;
            font-weight: 700;
            color: #374151;
        }

        .section {
            padding: 7px 0;
            border-bottom: 1px dashed #9ca3af;
        }

        .section-title {
            margin-bottom: 5px;
            padding: 3px 4px;
            background: #111827;
            color: #ffffff;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 0.4px;
            text-align: center;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin: 2px 0;
        }

        .row .label {
            flex: 0 0 43%;
            color: #374151;
            font-weight: 700;
        }

        .row .value {
            flex: 1;
            font-weight: 800;
            text-align: right;
            word-break: break-word;
        }

        .row-full {
            margin: 2px 0;
        }

        .row-full .label {
            color: #374151;
            font-weight: 700;
        }

        .row-full .value {
            margin-top: 1px;
            font-weight: 800;
            word-break: break-word;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
            margin: 4px 0;
        }

        .amount-label {
            flex: 1;
            color: #111827;
            font-weight: 800;
        }

        .amount-value {
            min-width: 26mm;
            text-align: right;
            font-weight: 900;
        }

        .total-box {
            margin-top: 7px;
            padding: 8px 6px;
            border: 2px solid #111827;
            border-radius: 5px;
            background: #f9fafb;
            text-align: center;
        }

        .total-label {
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 0.4px;
        }

        .total-value {
            margin-top: 2px;
            font-size: 22px;
            font-weight: 900;
            line-height: 1.05;
        }

        .notice {
            margin-top: 7px;
            padding: 6px 5px;
            border: 1px dashed #6b7280;
            border-radius: 4px;
            font-size: 9.5px;
            font-weight: 700;
            text-align: center;
            color: #374151;
        }

        .footer {
            padding-top: 7px;
            text-align: center;
            font-size: 9.5px;
            font-weight: 700;
            color: #374151;
        }

        .cut {
            margin-top: 10px;
            border-top: 1px dashed #111827;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
        }

        .no-print {
            display: block;
            width: 78mm;
            max-width: 78mm;
            margin: 10px auto;
            padding: 0 7px;
        }

        .print-button {
            width: 100%;
            border: 0;
            border-radius: 8px;
            background: #111827;
            color: #ffffff;
            padding: 10px;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .ticket {
                margin: 0;
                width: 78mm;
                max-width: 78mm;
            }
        }
    </style>
</head>

<body>
@php
    $folio = $pawn->formatted_folio ?? $pawn->folio;

    $customerName = $customer?->name ?: 'N/A';
    $customerCode = $customer?->id ?: 'N/A';
    $customerAddress = $customer?->address ?: 'N/A';
    $customerCity = collect([$customer?->city, $customer?->state])->filter()->join(' ') ?: 'N/A';

    $principal = (float) ($payment['principal'] ?? $pawn->total ?? 0);
    $interestToPay = (float) ($payment['interest_to_pay'] ?? 0);
    $iva = (float) ($payment['iva'] ?? 0);
    $paidAmount = (float) ($payment['paid_amount'] ?? 0);
    $inapamDiscount = (float) ($payment['inapam_discount'] ?? 0);
    $amountToLiquidate = (float) ($payment['amount_to_liquidate'] ?? 0);
    $daysToPay = (int) ($payment['days_to_pay'] ?? 0);

    $selectedDate = $new instanceof \Carbon\CarbonInterface
        ? $new
        : \Carbon\Carbon::parse($new ?? now());

    $expirationDate = $pawn->date_expiration
        ? \Carbon\Carbon::parse($pawn->date_expiration)
        : null;
@endphp

<div class="no-print">
    <button type="button" class="print-button" onclick="window.print()">
        Imprimir ticket
    </button>
</div>

<div class="ticket">
    <div class="header center">
        <div class="company">
            {!! nl2br(e($companyDescription ?? $company?->description ?? $company?->name ?? 'SICEM')) !!}
        </div>

        <div class="title-box">
            <h1 class="title">Fecha anticipada</h1>
            <div class="subtitle">Cálculo estimado de liquidación</div>
        </div>
    </div>

    <div class="section">
        <div class="row">
            <div class="label">Fecha emisión</div>
            <div class="value">{{ now()->format('d/m/Y') }}</div>
        </div>

        <div class="row">
            <div class="label">Hora</div>
            <div class="value">{{ now()->format('H:i:s') }}</div>
        </div>

        <div class="row">
            <div class="label">Folio</div>
            <div class="value">{{ $folio }}</div>
        </div>

        <div class="row">
            <div class="label">Sucursal</div>
            <div class="value">{{ $pawn->office?->name ?: 'N/A' }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Datos del cliente</div>

        <div class="row">
            <div class="label">Cliente</div>
            <div class="value">{{ $customerCode }}</div>
        </div>

        <div class="row-full">
            <div class="label">Nombre</div>
            <div class="value">{{ $customerName }}</div>
        </div>

        <div class="row-full">
            <div class="label">Dirección</div>
            <div class="value">{{ $customerAddress }}</div>
        </div>

        <div class="row-full">
            <div class="label">Población</div>
            <div class="value">{{ $customerCity }}</div>
        </div>

        @if($pawn->inapam_rate)
            <div class="row">
                <div class="label">Desc. INAPAM</div>
                <div class="value">{{ number_format((float) $pawn->inapam_rate * 100, 2) }}%</div>
            </div>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Cálculo</div>

        <div class="amount-row">
            <div class="amount-label">Importe del préstamo</div>
            <div class="amount-value">$ {{ number_format($principal, 2) }}</div>
        </div>

        <div class="amount-row">
            <div class="amount-label">Interés calculado</div>
            <div class="amount-value">$ {{ number_format($interestToPay, 2) }}</div>
        </div>

        <div class="amount-row">
            <div class="amount-label">IVA incluido en interés</div>
            <div class="amount-value">$ {{ number_format($iva, 2) }}</div>
        </div>

        @if($inapamDiscount > 0)
            <div class="amount-row">
                <div class="amount-label">Descuento INAPAM</div>
                <div class="amount-value">- $ {{ number_format($inapamDiscount, 2) }}</div>
            </div>
        @endif

        @if($paidAmount > 0)
            <div class="amount-row">
                <div class="amount-label">Abonos previos</div>
                <div class="amount-value">- $ {{ number_format($paidAmount, 2) }}</div>
            </div>
        @endif

        <div class="total-box">
            <div class="total-label">Importe al desempeñar</div>
            <div class="total-value">$ {{ number_format($amountToLiquidate, 2) }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Fechas consideradas</div>

        <div class="row">
            <div class="label">Calculado hasta</div>
            <div class="value">{{ $selectedDate->format('d/m/Y') }}</div>
        </div>

        <div class="row">
            <div class="label">Vencimiento</div>
            <div class="value">{{ $expirationDate ? $expirationDate->format('d/m/Y') : 'N/A' }}</div>
        </div>

        <div class="row">
            <div class="label">Días cobrados</div>
            <div class="value">{{ $daysToPay }}</div>
        </div>
    </div>

    <div class="notice">
        Este ticket es informativo. El importe puede variar si se realizan pagos,
        refrendos, descuentos, cancelaciones o cambios posteriores.
    </div>

    <div class="footer">
        Gracias por su preferencia.
    </div>

    <div class="cut">
        Corte aquí
    </div>
</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            window.print();
        }, 300);
    });
</script>
</body>
</html>