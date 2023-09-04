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
            <table class="w-100 ">
                <tr>
                    <td>
                        <div >
           <img src="{{public_path('logosacas.png')}}" style="width: 90px; height: 90px" alt="logo2" >
        </div>
                    </td>
                    <td>
                        <div style="padding-left: 10px ">
                            <p style="font-size: 15px;text-align: left;margin-bottom:none; margin-top:21px"><strong>SOLUCIONES AGRÍCOLAS CARDENAS SAAVEDRA S.A.S</strong>
                            </p>
                            <p style="font-size: 15px;text-align: left;margin:none"><strong>901.308.542-2</strong> </p>
                            <p style="font-size: 15px;text-align: left;margin:none">Dirección de correspondencia av. Quebrada seca #
                                19-41</p>
                        </div>
                    </td>
                </tr>
            </table>
            <table class="w-100 mt-4">
                <tr>
                    <td>
                        <strong><p  style="font-size: 15px; text-align: left ">SIMULADOR DE CRÉDITO</p></strong>
                    </td>
                    <td>
                        <p >{{$day}}</p>
                    </td>
                </tr>
            </table>
            <table class="w-100" style="margin-bottom: 30px">
                <tbody>
                    <tr>
                        <td class="td-left w-td-2">
                            <div
                                style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                <strong>Cantidad </strong> ${{ $amount}}
                            </div>
                        </td>
                        <td class="td-left w-td-2">
                            <div
                                style="padding-left: 2px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                <strong>Cuota inicial </strong> ${{ $initialFee}}
                            </div>
                        </td>
                        <td class="td-left  w-td-2">
                            <div
                                style="padding-left: 2px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                <strong>Número Cuotas </strong> {{ $numberFees}}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="td-left w-td-2">
                            <div
                                style="padding-left: 5px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                <strong>Tasa Interés (EA) </strong>%{{$interestRate}}
                            </div>
                        </td>
                        <td class="td-left w-td-2">
                            <div
                                style="padding-left: 2px; padding-right: 2px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; ">
                                <strong style="font-family: 'Montserrat', sans-serif;">Cuota Fija </strong> ${{number_format($fixAmount)}}

                            </div>
                        </td>
                        <td class="td-left">

                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="w-100 jojo " style="border-bottom: 2px solid #ccc">
                <thead class="h-40 thehead text-white header-info">
                    <tr class="h-40">
                        <th style="font-size: 15px" class="h-40">Cuota</th>
                        <th style="font-size: 15px">Fecha</th>
                        <th style="font-size: 15px" >Valor cuota mensual</th>
                        <th style="font-size: 15px" >valor interés</th>
                        <th style="font-size: 15px" >Valor capital</th>
                        <th style="font-size: 15px" >Saldo de crédito</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($fees as $fee)
                        <tr style=" padding-top:-1px">
                            <td class="td-center">{{ $fee['number'] }}</td>
                            <td class="td-center">{{ $fee['date'] }}</td>
                            <td class="td-center">${{ number_format($fee['cuota']) }}</td>
                            <td class="td-center">${{ number_format($fee['intereses']) }}</td>
                            <td class="td-center">${{ number_format($fee['amortizacion']) }}</td>
                            <td class="td-center">${{ number_format($fee['deuda']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
        margin-top: 240px;
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



    .letter-s {
        letter-spacing: 0.5px;
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
