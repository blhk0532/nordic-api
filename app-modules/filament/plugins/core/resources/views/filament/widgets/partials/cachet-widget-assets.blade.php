@once
    @php
        $theme = new \Cachet\Data\Cachet\ThemeData(app(\Cachet\Settings\ThemeSettings::class));
        $customizationSettings = app(\Cachet\Settings\CustomizationSettings::class);
    @endphp

    @vite(['resources/css/cachet.css'], 'vendor/cachethq/cachet/build')
    <script src="{{ asset('vendor/cachethq/cachet/cachet.js') }}" defer></script>

    <style type="text/css">
        {{ $theme->styles }}

        {!! $customizationSettings->stylesheet !!}
    </style>
@endonce
