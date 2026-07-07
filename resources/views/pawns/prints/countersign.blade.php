<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ticket refrendo {{ $folio }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #ffffff;
            color: #111111;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 10mm 12mm;
            background: #ffffff;
        }

        .header {
            text-align: center;
            font-size: 10px;
            line-height: 1.25;
            font-weight: bold;
            border-bottom: 1px solid #999;
            padding-bottom: 4px;
        }

        .title {
            font-size: 13px;
            letter-spacing: 2px;
            margin-top: 2px;
            font-weight: 900;
        }

        .folio {
            font-size: 10px;
            margin-top: 2px;
        }

        .section {
            border-bottom: 1px solid #cfcfcf;
            padding: 6px 0;
        }

        .row {
            display: grid;
            grid-template-columns: 130px 1fr 130px 1fr;
            gap: 4px 10px;
            line-height: 1.4;
        }

        .label {
            font-weight: bold;
            text-transform: uppercase;
        }

        .value {
            text-transform: uppercase;
        }

        .items-table,
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .items-table th,
        .items-table td,
        .totals-table th,
        .totals-table td {
            border: 1px solid #cfcfcf;
            padding: 4px;
            vertical-align: top;
        }

        .items-table th,
        .totals-table th {
            background: #f1f1f1;
            font-size: 10px;
            text-transform: uppercase;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .total-box {
            margin-top: 10px;
            text-align: right;
            font-size: 14px;
            font-weight: 900;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 45px;
        }

        .signature {
            border-top: 1px solid #111;
            text-align: center;
            padding-top: 5px;
            font-size: 10px;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 18px;
            font-size: 10px;
            line-height: 1.35;
            text-align: justify;
        }

        .print-actions {
            position: fixed;
            right: 20px;
            bottom: 20px;
            display: flex;
            gap: 8px;
        }

        .print-actions button {
            border: 0;
            border-radius: 8px;
            padding: 10px 14px;
            font-weight: bold;
            cursor: pointer;
        }

        .print-button {
            background: #2563eb;
            color: white;
        }

        .close-button {
            background: #e5e7eb;
            color: #111827;
        }

        @media print {
            @page {
                size: letter portrait;
                margin: 8mm;
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

            .print-actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            {{ strtoupper(config('app.name', 'SICEM')) }} CASA DE EMPEÑO<br>
            {{ strtoupper($office?->address ?? 'DIRECCIÓN NO CONFIGURADA') }}<br>
            @if($office?->phone)
                TEL. {{ $office->phone }}<br>
            @endif

            <div class="title">*** REFRENDO ***</div>
            <div class="folio">FOLIO: {{ $folio }}</div>
        </div>

        <div class="section">
            <strong>{{ $date }}</strong>
        </div>

        <div class="section">
            <div class="row">
                <div class="label">Cliente:</div>
                <div class="value">{{ $customer?->id }}</div>

                <div class="label">Fecha:</div>
                <div class="value">{{ optional($pawn->created_at)->format('d-m-Y') }}</div>

                <div class="label">Nombre:</div>
                <div class="value">{{ $customer?->name }}</div>

                <div class="label">Vencimiento:</div>
                <div class="value">{{ optional($pawn->date_expiration)->format('d-m-Y') }}</div>

                <div class="label">Dirección:</div>
                <div class="value">
                    {{ trim(($customer?->address ?? '') . ' ' . ($customer?->city ?? '') . ' ' . ($customer?->state ?? '')) }}
                </div>

                <div class="label">Remate:</div>
                <div class="value">{{ optional($pawn->date_auction)->format('d-m-Y') }}</div>

                <div class="label">Población:</div>
                <div class="value">{{ $customer?->city }} {{ $customer?->state }}</div>

                <div class="label">Días cobrados:</div>
                <div class="value">{{ $daysToPay }}</div>
            </div>
        </div>

        <div class="section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 100px;">Cantidad</th>
                        <th>Descripción de la prenda</th>
                        <th style="width: 130px;">Préstamo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td class="center">
                                {{ number_format((float) $item->quantity, 3) }}
                                {{ $item->product?->unit }}
                            </td>
                            <td>
                                <strong>{{ $item->product?->description }}</strong><br>
                                {{ $item->description }}
                            </td>
                            <td class="right">
                                ${{ number_format((float) $item->value, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="center">Sin prendas registradas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="section">
            <table class="totals-table">
                <tbody>
                    <tr>
                        <th class="right">Préstamo original</th>
                        <td class="right">${{ number_format((float) $pawn->total, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="right">Tasa diaria %</th>
                        <td class="right">{{ number_format((float) $pawn->daily_interest_rate, 3) }}</td>
                    </tr>
                    <tr>
                        <th class="right">Interés sin IVA</th>
                        <td class="right">${{ number_format($interestWithoutIva, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="right">IVA</th>
                        <td class="right">${{ number_format($iva, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="right">Interés a pagar por refrendo</th>
                        <td class="right">${{ number_format($interest, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="right">Abono a capital</th>
                        <td class="right">${{ number_format($payExtra, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="right">Nuevo préstamo</th>
                        <td class="right">${{ number_format($newPrincipal, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="total-box">
                PAGO TOTAL ${{ number_format($totalToPay, 2) }}
            </div>
        </div>

        <div class="section">
            <strong>HORA:</strong> {{ $time }}<br>
            <strong>VALUADOR:</strong> {{ strtoupper($cashier ?? 'N/A') }}
        </div>

        <div class="signatures">
            <div class="signature">
                Firma del cliente
            </div>

            <div class="signature">
                Firma del valuador
            </div>
        </div>

        <div class="footer">
            Este documento corresponde al ticket informativo de refrendo del contrato prendario indicado.
            Los importes mostrados están calculados con base en la fecha de emisión, días cobrados,
            tasa diaria, IVA y condiciones registradas en el sistema.
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
            }, 400)
        })
    </script>
</body>
</html>