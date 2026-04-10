@if ($refresh_rate)
    <meta http-equiv="refresh" content="{{ $refresh_rate }}">
@endif

{!! $cachet_header !!}

<style type="text/css">
    {{ $theme->styles }}

    {!! $cachet_css !!}
</style>
