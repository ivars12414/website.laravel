<!DOCTYPE html>
<html lang='{{ str_replace('_', '-', app()->getLocale()) }}'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <title>{{ $page->meta('title') ?? config('app.name') }}</title>
    @if($page->meta('description'))<meta name='description' content='{{ $page->meta('description') }}'>@endif
    @if($page->canonical())<link rel='canonical' href='{{ $page->canonical() }}'>@endif
    @foreach($page->alternates() as $lang => $url)
      <link rel='alternate' hreflang='{{ $lang }}' href='{{ $url }}'>
    @endforeach
</head>
<body>
    <div id='app'>
        @yield('content')
    </div>
</body>
</html>
