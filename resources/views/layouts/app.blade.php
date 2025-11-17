<!DOCTYPE html>
<html lang='{{ str_replace('_', '-', app()->getLocale()) }}'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <title>{{ $page->meta('title') ?? config('app.name') }}</title>
    @if($page->meta('description'))
        <meta name='description' content='{{ $page->meta('description') }}'>
    @endif
    @if($page->canonical())
        <link rel='canonical' href='{{ $page->canonical() }}'>
    @endif
    @foreach($page->alternates() as $lang => $url)
        <link rel='alternate' hreflang='{{ $lang }}' href='{{ $url }}'>
    @endforeach

    <!-- Google Fonts -->
    <!-- Preconnect до Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">

    <!-- Отложеное подключение без блокирования -->
    <link rel="preload" as="style"
          href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap"
          onload="this.rel='stylesheet'">

    <!-- CSS -->
    <!-- <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css"> -->
    <link rel="stylesheet" href="/build/css/style.min.css">

    <!-- JS -->
    <script src="/build/js/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/remodal/1.1.1/remodal.min.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.js"></script> -->
    <!-- <script type="text/javascript" src="https://unpkg.com/aos@next/dist/aos.js"></script> -->
    <script src="/build/js/main.js"></script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"
            integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css"
          integrity="sha512-3pIirOrwegjM6erE5gPSwkUzO+3cTjpnV9lexlNZqvupR64iZBnOOTiiLPb9M36zpMScbmUNIcHUqKD47M719g=="
          crossorigin="anonymous" referrerpolicy="no-referrer">

</head>
<body>
<div id='app'>
    @yield('content')
</div>
</body>
</html>
