<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $page->title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.17/dist/base.min.css" />
    @if ($css && strlen($css) > 200)
        <style>{!! \CybertronianKelvin\Graper\Helpers\GraperHelper::stripLayerDirectives($css) !!}</style>
    @endif
</head>
<body class="antialiased">
    <script src="https://cdn.tailwindcss.com"></script>
    {!! $html !!}
</body>
</html>