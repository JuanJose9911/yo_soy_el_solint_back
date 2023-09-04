<div style="font-family: 'sans-serif'; margin: 0 auto; max-width: 700px;">
     <div style="height: 100px; position: relative;">
        <img src="{{ asset('img/emails/header.png')}}" style="width: 100%; height: 100%; object-fit: cover">
        <img src="{{ asset('img/emails/logo.png')}}" style="width: 81px; height: 100%; object-fit: contain; position: absolute; top: 10%; left: 10%">
    </div>
    <div style="max-width: 70%; margin: 50px auto 80px;">
       @yield('content')
    </div>
    <div>
        <img src="{{ asset('img/emails/footer.png')}}" style="width: 100%; height: 50px; object-fit: cover">
    </div>
</div>
