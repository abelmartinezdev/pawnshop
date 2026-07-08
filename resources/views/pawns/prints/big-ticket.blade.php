<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Boleta {{ $folio }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #ffffff;
            color: #000000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8px;
            line-height: 1.2;
        }

        .page {
            width: 216mm;
            min-height: 279mm;
            margin: 0 auto;
            padding: 8mm 10mm;
            background: #ffffff;
        }

        .ticket {
            width: 100%;
            max-width: 190mm;
            margin: 0 auto;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: 700;
        }

        .black {
            font-weight: 900;
        }

        .upper {
            text-transform: uppercase;
        }

        .mb-2 {
            margin-bottom: 2px;
        }

        .mt-2 {
            margin-top: 2px;
        }

        .mt-4 {
            margin-top: 4px;
        }

        .mt-6 {
            margin-top: 6px;
        }

        .mt-8 {
            margin-top: 8px;
        }

        .small {
            font-size: 7px;
        }

        .tiny {
            font-size: 6.5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000000;
            padding: 3px;
            vertical-align: top;
        }

        th {
            background: #f2f2f2;
            text-align: center;
            font-weight: 900;
        }

        .no-border {
            border: 0 !important;
        }

        .soft {
            background: #f7fbfb;
        }

        .blue-soft {
            background: #eaf7fb;
        }

        .gray-soft {
            background: #eeeeee;
        }

        .title {
            font-size: 9px;
            font-weight: 900;
        }

        .folio {
            float: right;
            font-size: 8px;
            font-weight: 900;
        }

        .section-title {
            background: #f0f0f0;
            border: 1px solid #000000;
            padding: 3px;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
        }

        .legal {
            text-align: justify;
            font-size: 7px;
            line-height: 1.2;
        }

        .signature-line {
            height: 28px;
            border-bottom: 1px solid #000000;
            margin-bottom: 3px;
        }

        .print-actions {
            position: fixed;
            right: 18px;
            bottom: 18px;
            display: flex;
            gap: 8px;
        }

        .print-actions button {
            border: 0;
            border-radius: 8px;
            padding: 10px 14px;
            font-weight: 900;
            cursor: pointer;
            font-size: 12px;
        }

        .print-button {
            background: #2563eb;
            color: #ffffff;
        }

        .close-button {
            background: #e5e7eb;
            color: #111827;
        }

        @media print {
            @page {
                size: letter portrait;
                margin: 6mm;
            }

            body {
                background: #ffffff;
            }

            .page {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 0;
            }

            .ticket {
                max-width: none;
            }

            .print-actions {
                display: none;
            }
        }
    </style>
</head>
<body>
@php
    $money = fn ($value) => '$' . number_format((float) $value, 2);
    $percent = fn ($value, $decimals = 3) => number_format((float) $value, $decimals);
    $customerPhone = $customer?->mobile ?: $customer?->phone;
    $companyName = $company?->name ?: config('app.name', 'SICEM');
    $companyAddress = $company?->address ?: $office?->address;
    $companyPhone = $company?->phone ?: $office?->phone;
    $companyRfc = $company?->rfc ?: 'N/A';
@endphp

<div class="page">
    <div class="ticket">
        <div class="folio">
            FOLIO No. {{ $folio }}
        </div>

        <div class="text-center title upper">
            Fecha de celebración del contrato: {{ $dateLong ?: $date }}<br>
            CONTRATO DE MUTUO CON INTERÉS Y GARANTÍA PRENDARIA CELEBRADO POR UNA PARTE<br>
            {{ $companyName }}, en adelante EL PROVEEDOR, y por la otra parte {{ $customer?->name ?: 'CLIENTE NO ESPECIFICADO' }}, en adelante EL CONSUMIDOR.
        </div>

        <div class="legal mt-4">
            Este documento ampara la operación de préstamo con garantía prendaria registrada en el sistema. Los datos variables se toman de la base de datos y las cláusulas generales se muestran como texto fijo de referencia para impresión.
        </div>

        <table class="mt-4">
            <tr>
                <td style="width: 25%;">
                    <span class="bold">FECHA:</span> {{ $date }}
                </td>
                <td style="width: 25%;">
                    <span class="bold">CLIENTE:</span> {{ $customer?->id ?: 'N/A' }}
                </td>
                <td style="width: 25%;">
                    <span class="bold">BOLETA:</span> {{ $folio }}
                </td>
                <td style="width: 25%;">
                    <span class="bold">SUCURSAL:</span> {{ $office?->name ?: 'N/A' }}
                </td>
            </tr>
        </table>

        <table class="mt-2">
            <tr>
                <th style="width: 16%;">CAT COSTO ANUAL TOTAL</th>
                <th style="width: 14%;">TASA DE INTERÉS ANUAL</th>
                <th style="width: 18%;">MONTO DEL PRÉSTAMO<br>(MUTUO)</th>
                <th style="width: 18%;">MONTO TOTAL A PAGAR</th>
                <th>COMISIONES</th>
            </tr>
            <tr>
                <td class="text-center">
                    <strong>{{ $percent($catAnnualWithIva, 2) }}%</strong><br>
                    Para fines informativos y de comparación.
                </td>
                <td class="text-center">
                    {{ $percent($catAnnualNoIva, 2) }}%<br>
                    Sin I.V.A.
                </td>
                <td class="text-center black">
                    {{ $money($principal) }}<br>
                    Moneda Nacional
                </td>
                <td class="text-center">
                    <strong>{{ $money($totalToPayAtTerm) }}</strong><br>
                    Estimado al vencimiento del contrato.
                </td>
                <td>
                    <table>
                        <tr>
                            <td class="no-border bold">Almacenaje y custodia</td>
                            <td class="no-border text-right">{{ $money($storageCommission) }}</td>
                        </tr>
                        <tr>
                            <td class="no-border bold">Comercialización</td>
                            <td class="no-border text-right">{{ $money($marketingCommission) }}</td>
                        </tr>
                        <tr>
                            <td class="no-border bold">Desempeño extemporáneo</td>
                            <td class="no-border text-right">{{ $money($delayedPaymentCommission) }}</td>
                        </tr>
                        <tr>
                            <td class="no-border bold">Reposición de contrato</td>
                            <td class="no-border text-right">{{ $money($replacementContractCommission) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="legal mt-2">
            Metodología de cálculo de interés: tasa de interés diaria multiplicada por los días transcurridos y por el monto del préstamo. El I.V.A. se calcula sobre el interés generado. La información mostrada es estimada y puede variar por pagos, refrendos, descuentos, cancelaciones o cambios posteriores.
        </div>

        <div class="section-title mt-4">TOTAL A PAGAR</div>

        <table>
            <tr>
                <th style="width: 9%;">NÚMERO</th>
                <th style="width: 13%;">IMPORTE DEL MUTUO</th>
                <th style="width: 12%;">INTERÉS</th>
                <th style="width: 12%;">ALMACENAJE</th>
                <th style="width: 12%;">I.V.A.</th>
                <th style="width: 14%;">POR REFRENDO</th>
                <th style="width: 14%;">POR DESEMPEÑO</th>
                <th style="width: 14%;">CÁLCULO REALIZADO LOS DÍAS</th>
            </tr>

            @foreach($paymentOptions as $option)
                <tr>
                    <td class="text-center">{{ $option['number'] }}</td>
                    <td class="text-right">{{ $money($option['principal']) }}</td>
                    <td class="text-right">{{ $money($option['interest']) }}</td>
                    <td class="text-right">{{ $money($option['storage']) }}</td>
                    <td class="text-right">{{ $money($option['iva']) }}</td>
                    <td class="text-right black">{{ $money($option['for_refinance']) }}</td>
                    <td class="text-right black">{{ $money($option['for_liquidation']) }}</td>
                    <td class="text-center">{{ $option['date'] }}</td>
                </tr>
            @endforeach
        </table>

        <table class="mt-2">
            <tr>
                <th style="width: 50%;">COSTO MENSUAL TOTAL</th>
                <th style="width: 50%;">COSTO DIARIO TOTAL</th>
            </tr>
            <tr>
                <td class="text-center">
                    Para fines informativos y de comparación:
                    <strong>{{ $percent($monthlyInterestRate, 3) }}% fijo sin I.V.A.</strong>
                </td>
                <td class="text-center">
                    Para fines informativos y de comparación:
                    <strong>{{ $percent($dailyInterestRate, 3) }}% fijo sin I.V.A.</strong>
                </td>
            </tr>
        </table>

        <div class="legal mt-2">
            Comisión mínima {{ $money(0) }}. Código de capacidad de pago presentado: con declaración del consumidor bajo protesta de decir verdad. El proveedor informa que el consumidor puede desempeñar o refrendar conforme a las condiciones aquí indicadas.
        </div>

        <div class="section-title mt-4">DESCRIPCIÓN DE LA PRENDA</div>

        <table>
            <tr>
                <th style="width: 18%;">Descripción genérica</th>
                <th style="width: 40%;">Características</th>
                <th style="width: 14%;">Avalúo</th>
                <th style="width: 14%;">Préstamo</th>
                <th style="width: 14%;">% Préstamo sobre avalúo</th>
            </tr>

            @forelse($items as $item)
                <tr class="blue-soft">
                    <td>
                        {{ $item->product?->description ?: 'PRENDA' }}
                    </td>
                    <td>
                        {{ $item->description ?: 'SIN DESCRIPCIÓN' }}<br>
                        Cantidad: {{ number_format((float) $item->quantity, 3) }} {{ $item->product?->unit }}
                    </td>
                    <td class="text-right">
                        {{ $money($estimatedValue) }}
                    </td>
                    <td class="text-right black">
                        {{ $money($item->value) }}
                    </td>
                    <td class="text-center">
                        {{ $percent($loanRate, 2) }}%
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Sin prendas registradas</td>
                </tr>
            @endforelse
        </table>

        <table class="mt-2">
            <tr>
                <td style="width: 35%;" class="bold">Monto del avalúo</td>
                <td>{{ $money($estimatedValue) }}</td>
            </tr>
            <tr>
                <td class="bold">Porcentaje del préstamo sobre el avalúo</td>
                <td>{{ $percent($loanRate, 2) }}%</td>
            </tr>
            <tr>
                <td class="bold">Fecha de inicio de comercialización</td>
                <td>{{ $dateAuction ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td class="bold">Plazo máximo de refrendo</td>
                <td>{{ $term }} días</td>
            </tr>
            <tr>
                <td class="bold">Fecha límite de desempeño</td>
                <td>{{ $dateExpiration ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td class="bold">Estas conceptos causarán el pago del impuesto al valor agregado</td>
                <td>I.V.A. calculado al {{ $percent($ivaRate, 2) }}%</td>
            </tr>
        </table>

        <div class="legal mt-4">
            * El prestatario manifiesta que la prenda entregada es de su legítima propiedad y que no proviene de hecho ilícito alguno. Para cualquier aclaración relacionada con esta operación deberá acudir a la sucursal emisora con identificación oficial y la presente boleta.
        </div>

        <table class="mt-4">
            <tr>
                <td style="width: 50%;">
                    <strong>Para cualquier duda, aclaración o reclamación favor de dirigirse a:</strong><br>
                    {{ $companyName }}<br>
                    {{ $companyAddress ?: 'DIRECCIÓN NO CONFIGURADA' }}<br>
                    Tel. {{ $companyPhone ?: 'N/A' }}<br>
                    RFC: {{ $companyRfc }}
                </td>
                <td>
                    <strong>Datos del consumidor</strong><br>
                    Nombre: {{ $customer?->name ?: 'N/A' }}<br>
                    Dirección: {{ $customerAddress }}<br>
                    Teléfono: {{ $customerPhone ?: 'N/A' }}<br>
                    RFC: {{ $customer?->rfc ?: 'N/A' }}<br>
                    Identificación: {{ $customer?->code_id ?: 'N/A' }}
                </td>
            </tr>
        </table>

        <div class="section-title mt-4">BENEFICIO</div>

        <table>
            <tr>
                <td style="width: 50%;">
                    El consumidor podrá designar beneficiario para efectos de esta operación.
                    <br>
                    <strong>Beneficiario:</strong> {{ $pawn->beneficiary ?: 'NO CAPTURADO' }}
                </td>
                <td>
                    <strong>Bolsa:</strong> {{ $pawn->bag ?: 'NO CAPTURADA' }}<br>
                    <strong>Vencimiento:</strong> {{ $dateExpiration ?: 'N/A' }}<br>
                    <strong>Remate:</strong> {{ $dateAuction ?: 'N/A' }}
                </td>
            </tr>
        </table>

        <div class="section-title mt-4">FIRMAS</div>

        <table>
            <tr>
                <td style="width: 50%; height: 58px;">
                    <div class="signature-line"></div>
                    <div class="text-center bold">EL CONSUMIDOR</div>
                    <div class="text-center">{{ $customer?->name ?: 'N/A' }}</div>
                </td>
                <td style="width: 50%; height: 58px;">
                    <div class="signature-line"></div>
                    <div class="text-center bold">EL PROVEEDOR</div>
                    <div class="text-center">{{ $cashier ?: $companyName }}</div>
                </td>
            </tr>
        </table>

        <div class="legal mt-4">
            EL HORARIO DE SERVICIO AL PÚBLICO EN ESTE ESTABLECIMIENTO ES EL REGISTRADO POR LA SUCURSAL.
            La presente boleta se expide para constancia de las partes. El consumidor recibe copia de este documento y manifiesta estar conforme con el contenido de las condiciones señaladas.
        </div>

        <div class="tiny mt-8">
            Impreso: {{ now()->format('d-m-Y H:i:s') }} · Sistema SICEM
        </div>
    </div>
</div>

<div class="print-actions">
    <button type="button" class="print-button" onclick="window.print()">Imprimir</button>
    <button type="button" class="close-button" onclick="window.close()">Cerrar</button>
</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            window.print()
        }, 500)
    })
</script>
</body>
</html>