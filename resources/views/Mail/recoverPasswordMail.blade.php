@extends('mail.Partials.layout')

@section('content')
    <p style="font-size: 12px">
        Estimado usuario.
        <br><br>
        Hemos recibido una solicitud para cambiar su contraseña
        <br><br><br><br>
        Su codigo de verificación es:
        <br><br><br>
    </p>
    <p style="width: fit-content; margin: 0 auto">
        <strong style="font-size: 18px; text-transform: uppercase; margin: 0 auto;">
            {{$data['code']}}
        </strong>
    </p>
@endsection
