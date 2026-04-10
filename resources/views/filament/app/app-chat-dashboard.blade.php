<x-filament-panels::page>
    <style>
            .fi-main.fi-width-full {
        padding: 0rem !important;
        background: #18181b;
    }
    .fi-page-header-main-ctn {
        padding: 0rem !important;
    }
    .fi-sc-component{
        border-left: 1px solid #4f4f56ad;
    }
    div.fi-section .fi-loading-section {
        min-height: 96vh!important;
        border-radius: 0px!important;
    }
    </style>
    {{ $this->content }}
</x-filament-panels::page>
