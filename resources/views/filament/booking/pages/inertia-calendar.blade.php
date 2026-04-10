<x-filament-panels::page>
    <style>
        .fi-page-header-main-ctn{
            padding: 0rem !important;
        }
        .fi-main.fi-width-full{
            padding: 0rem !important;
            background: #18181b;
        }
        .fi-page-content {
            padding: 0rem !important;
        }
        main.relative{
               padding: 0rem !important;
               margin: 0rem !important;
        }
        h1.fi-header-heading {
display:none!important;
}
.fi-header{
display:none!important;
}
    </style>
    @php
        $appUrl = \App\Filament\App\Pages\InertiaCalendar::getAppUrl();
    @endphp
<div class="h-full">
    <iframe
        style="border-width: 0; min-width: 100%; width: 100%; min-height: calc(100vh - 4rem); max-height: calc(100vh - 4rem); height: calc(100vh - 4rem); overflow: hidden;"
        class="h-full"
        src="{{ $appUrl }}"
        frameborder="0"
        scrolling="no">
    </iframe>
</div>
</x-filament-panels::page>
