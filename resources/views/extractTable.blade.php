<!DOCTYPE html>
<html lang="en">
<div class="card" style="border:none">

    <head>
        <title>Pdf Pagos</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
            integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    </head>

    <body>
        <div>
            <div>
                <table class="w-100 ">
                    <tr>
                        <td>
                            <div>
                                <img src="{{ public_path('logosacas.png') }}" style="width: 90px; height: 90px"
                                    alt="logo2">
                            </div>
                        </td>
                        <td>
                            <div style="padding-left: 10px ">
                                <p style="font-size: 15px;text-align: left;margin-bottom:0; margin-top:21px">
                                    <strong>SOLUCIONES
                                        AGRÍCOLAS CARDENAS SAAVEDRA S.A.S</strong>
                                </p>
                                <p style="font-size: 15px;text-align: left;margin:0"><strong>901.308.542-2</strong></p>
                                <p style="font-size: 15px;text-align: left;margin:0">Dirección de correspondencia av.
                                    Quebrada seca #
                                    19-41</p>
                            </div>
                        </td>
                    </tr>
                </table>
                <table class="w-100 mt-4">
                    <tr>
                        <td>
                            <strong>
                                <p style="font-size: 15px; text-align: left ">EXTRACTO DEL CREDITO Nro. {{ $credit_id }}</p>
                            </strong>
                        </td>
                        <td>
                            <p>12/12/22</p>
                        </td>
                    </tr>
                </table>

                <!-- tabla datos de contacto -->
                <div class="border-table w-fit">
                    <table class="w-100">
                        <thead class="thead-light w-100">
                            <div class="relative w-100 box-base ">
                                <div class="absolute cabecera text-white header-info w-alto"> DATOS DE CONTACTO</div>
                            </div>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                        <strong> Cliente:</strong> {{ $client_id }}
                                    </div>
                                </td>
                                <td class="td-left w-td-2">
                                    <div class="w-100"
                                        style="padding-left: 5px;overflow-wrap: break-word; padding-right: 2px;">
                                        {{ $client_name }}
                                    </div>
                                </td>
                                <td class="td-left"></td>

                            </tr>
                            <tr>

                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; overflow-wrap: break-word;">
                                        <strong> Dirección:</strong> {{ $client_address }}
                                    </div>
                                </td>
                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; ">
                                        <strong> Teléfono:</strong> {{ $client_phone }}
                                    </div>
                                </td>

                                <td class="td-left"><strong style="overflow-wrap: break-word;">{{ $client_city }}</strong></td>

                            </tr>


                        </tbody>
                    </table>
                </div>
                <!-- fin tabla datos de contacto -->

                 <!-- tabla pago actual -->
                 <div class="border-table w-fit">
                    <table class="w-100">
                        <thead class="thead-light w-100">
                            <div class="relative w-100 box-base ">
                                <div class="absolute cabecera text-white header-info w-alto"> PAGO ACTUAL</div>
                            </div>
                        </thead>
                        <tbody>


                            <tr>

                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                        <strong> Fecha de corte:</strong> {{ $date_cut }}
                                    </div>
                                </td>
                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; ">
                                        <strong> Fecha de vencimiento:</strong> {{ $pay_date }}
                                    </div>
                                </td>
                                <td></td>


                            </tr>
                            <tr>

                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                        <strong> Estado:</strong> {{ $pay_status }}
                                    </div>
                                </td>
                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px;padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; ">
                                        <strong> Total a pagar:</strong> {{ number_format($pay_total) }}
                                    </div>
                                </td>

                                <td></td>


                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- fin tabla pago actual -->
                <!-- informacion actual del credito -->
                <div class="border-table w-fit">
                    <table class="w-100 ">
                        <thead class="thead-light ">
                            <div class="relative w-100 box-base ">
                                <div class="absolute cabecera text-white header-info w-alto"> INFORMACIÓN ACTUAL DEL
                                    CRÉDITO
                                </div>
                            </div>
                        </thead>
                        <tbody>
                            <tr>


                                <td class="border-right">
                                    <div class="td-right relative" style="padding-left: 5px; padding-right: 2.5px">
                                        <strong class="absolute"> Pagaré nro</strong> {{ $pay_number }}
                                    </div>
                                </td>
                                <td class="border-right">
                                    <div class="td-right relative" style="padding-left: 2.5px; padding-right: 2.5px">
                                        <strong class="absolute"> Desembolso</strong> {{ $outlay }}
                                    </div>
                                </td>

                                <td>
                                    <div class="td-bet flex  text-justify "><strong class="w-100"> Tasas de interés
                                            Vigente
                                            %EA</strong></div>
                                </td>

                            </tr>
                            <tr>
                                <td class="border-right">
                                    <div class="td-right relative" style="padding-left: 5px; padding-right: 2.5px">
                                        <strong class="absolute"> Creación</strong> {{ $create_date }}
                                    </div>
                                </td>
                                <td class="border-right">
                                    <div class="td-right relative" style="padding-left: 2.5px; padding-right: 2.5px">
                                        <strong class="absolute"> Saldo a Capital</strong>
                                        {{ number_format($capital_balance) }}
                                    </div>
                                </td>

                                <td class="border-right">
                                    <div class="td-right relative" style="padding-left: 2.5px; padding-right: 5px">
                                        <strong class="absolute">Corriente</strong> {{ $annual_interest_rate }}
                                    </div>

                                </td>

                            </tr>
                            <tr>

                                <td class="border-right">
                                    <div class="td-right relative" style="padding-left: 5px; padding-right: 2.5px">
                                        <strong class="absolute"> Cuota inicial</strong>
                                        {{ number_format($init_value) }}
                                    </div>
                                </td>
                                <td class="border-right">
                                    <div class="td-right relative" style="padding-left: 2.5px; padding-right: 2.5px">
                                        <strong class="absolute">Cuotas pendientes</strong> {{ $canceled_installment }}
                                    </div>
                                </td>
                                <td class="border-right">
                                    <div class="td-right relative" style="padding-left: 2.5px; padding-right: 5px">
                                    </div>
                                </td>

                            </tr>
                            <tr>
                                <td class="border-right">
                                    <div class="td-right relative" style="padding-left: 5px; padding-right: 2.5px">
                                        <div style="width: 5.5cm;"><strong class="absolute"> Plazo pactado</strong>
                                        </div>
                                        {{ $agreed_term }}
                                    </div>
                                </td>
                                <td class="border-right">
                                    <div class="td-right relative" style="padding-left: 2.5px; padding-right: 2.5px">
                                        <div style="width: 5.5cm;"><strong class="absolute">Cuotas canceladas</strong>
                                        </div>
                                        {{ $outstanding_installment }}
                                    </div>
                                </td>

                                </td>

                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- fin informacion actual del credito -->
                <!-- tabla informacion de pagos -->
                <div class="w-fit border-table ">
                    <table class="w-100 ">
                        <thead class="thead-light">
                            <div class="relative w-100 box-base ">
                                <div class="absolute cabecera text-white header-info w-alto"> INFORMACIÓN DE PAGOS
                                </div>
                            </div>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="td-left w-td-2">

                                </td>
                                <td class="td-left w-td-2">
                                    <div class="w-100"
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                        <strong> Ultimo pago</strong>
                                    </div>
                                </td>
                                <td class="td-left w-td-2">
                                    <div class="w-100"
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                        <strong> pago a realizar</strong>
                                    </div>
                                </td>
                            </tr>
                            <tr>

                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                        <strong> Abono a Capital</strong>
                                    </div>
                                </td>
                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; ">
                                        ${{ number_format($last_capital) }}
                                    </div>
                                </td>

                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; ">
                                        ${{ number_format($actual_capital) }}
                                    </div>
                                </td>

                            </tr>
                            <tr>

                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                        <strong> Interes corriente</strong>
                                    </div>
                                </td>
                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; ">
                                        ${{ number_format($last_interest) }}
                                    </div>
                                </td>

                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; ">
                                        ${{ number_format($actual_interest) }}
                                    </div>
                                </td>

                            </tr>
                            <tr>

                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                        <strong> interes Mora</strong>
                                    </div>
                                </td>
                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; ">
                                        ${{ number_format($last_mora) }}
                                    </div>
                                </td>

                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; ">
                                        ${{ number_format($actual_mora) }}
                                    </div>
                                </td>

                            </tr>
                            <tr>

                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                        <strong> Valor Total</strong>
                                    </div>
                                </td>
                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; ">
                                        ${{ number_format($last_total) }}
                                    </div>
                                </td>

                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; ">
                                        ${{ number_format($actual_total) }}
                                    </div>
                                </td>

                            </tr>
                            <tr>

                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                        <strong> Fecha ultimo pago</strong>
                                    </div>
                                </td>
                                <td class="td-left w-td-2">
                                    <div
                                        style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; ">
                                        {{ $last_pay_date }}
                                    </div>
                                </td>


                            </tr>
                        </tbody>

                    </table>
                </div>
                <!-- fin tabla informacion de pagos -->

                {{-- <!-- firmas -->
                <div class="w-100 mt-ekis ">
                    <table class="w-100 ">
                        <tr>
                            <td class="w-100 ">
                                <div class=" firma margin-auto td-center"> Aceptado</div>
                            </td>
                            <td class="w-100 ">
                                <div class=" firma margin-auto td-center">Preparado</div>
                            </td>
                            <td class="w-100 ">
                                <div class=" firma margin-auto td-center">Aprobado</div>
                            </td>
                        </tr>
                    </table>

                </div>
                <!-- fin firmas --> --}}

            </div>

    </body>
</div>

</html>

<style>
    .w-td {
        width: 5.5cm;
    }

    .w-td-2 {
        width: 6cm;
    }

    .margin-auto {
        margin: auto;
    }

    .mb-aply {
        margin-bottom: 16px;
    }

    .box-base {
        width: 0.5cm;
        height: 0.9cm;
    }

    .top-fecha {
        top: -10px;
        left: -2px;
    }

    .w-alto {
        width: 18.3cm;
    }

    .flex {
        display: flex;
    }

    .w-100 {
        width: 100%;
    }

    .text-justify {
        text-align: center;
    }

    .absolute {
        position: absolute;
    }

    .relative {
        position: relative;
    }

    .mt-ekis {
        margin-top: 230px;
    }

    .firma {
        border-top: 2px #ccc solid;
        width: 4.5cm
    }

    .tr-bottom {
        border-bottom: 1px solid #ccc;
    }

    .mb-10 {
        margin-bottom: 10px;
    }

    .mt-17 {
        margin-top: 17px;
    }

    .mt-20 {
        margin-top: 30px;
    }

    .h-40 {
        height: 40px !important;
    }

    tr th {

        text-align: center;
    }

    tr th:first-child {
        border-radius: 6px 0px 0px 6px;
    }

    tr th:last-child {
        border-radius: 0px 6px 6px 0px;
    }

    .border-right {
        border-right: 1px solid #ccc;
    }

    .thehead {
        background-color: #694DF9;
        border-radius: 10px;
        padding: 2cm !important;
        font-size: 20px !important;
        font-weight: bold;
        text-align: center;
    }

    .table {
        margin-bottom: none !important;
    }

    tr td {
        border: none !important;
        padding: 2px !important;
    }

    .border-table {
        margin-bottom: 12px;
        border: 1px solid #ccc;
        border-radius: 10px;
        border-collapse: collapse;
        width: 100%;
    }

    .text-white {
        color: white;
        font-weight: bold;
        font-size: 15px !important;

    }

    .cabecera {
        background-color: #694DF9;
        padding: 5px;
        border-radius: 10px;
        border: 1px solid #694DF9;
        font-size: 12px;
        font-weight: bold;
        text-align: center;
    }

    th {
        text-align: center;
    }

    .td-center {
        text-align: center;
    }

    .n-fecha {
        width: 6cm;
    }

    .td-bet {
        justify-content: space-between;

    }

    .td-even {
        justify-content: space-evenly;
    }

    .td-right {
        text-align: right;
    }

    .td-left {
        text-align: left;
    }

    header-info .info {

        margin-top: 27px;
    }

    .letter-s {
        letter-spacing: 0.5px;
    }

    .header-info {
        font-size: 15px !important;
        text-align: left;
    }

    p {
        font-size: 11px;
        text-align: right;
    }

    .logo {
        width: 2cm;
        height: 2cm;
        border: 2px black solid
    }

    .card {
        width: 100%;
        height: 27.2cm;

    }
</style>
