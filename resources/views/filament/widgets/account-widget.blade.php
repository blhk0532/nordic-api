@php
    $user = filament()->auth()->user();
@endphp
<style>
    div.fi-ta-header.fi-ta-header-adaptive-actions-position{
         display: none!important;
    }
div.fi-page div.fi-page-header-main-ctn header.fi-header{
       padding: 0rem !important;
       display: none!important;
   }

   div.flex.items-center.justify-end.flex-shrink-0{
       display: none!important;
   }
   .fi-avatar.fi-circular.fi-size-lg.fi-user-avatar {
         width: 46px;
         height: 46px;
   }
</style>
<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        @php
            $isOnline = $user->isOnline();
        @endphp
        <div class="relative inline-flex">
            <x-filament-panels::avatar.user
                size="lg"
                :user="$user"
                loading="lazy"
            />
            <div
                @class([
                    'absolute bottom-0 right-0 h-3.5 w-3.5 rounded-full border-2 border-white dark:border-gray-900',
                    'bg-green-500' => $isOnline,
                    'bg-gray-500' => !$isOnline,
                ])
                style="{{ $isOnline ? 'background-color: #22c55e;' : 'background-color: #6b7280;' }}"
            ></div>
        </div>

        <div class="fi-account-widget-main">
            <h2 class="fi-account-widget-heading">
                 {{ filament()->getUserName($user) }}
            </h2>

            <p class="fi-account-widget-user-name flex items-center gap-x-1.5 text-sm">
                {{ $user->active_status?->getLabel() ?? 'Offline' }}
                @if($user->active_at)
                    <span class="text-gray-500 italic text-xs">
                        • {{ $user->active_at->diffForHumans() }}
                    </span>
                @endif
            </p>
        </div>

    <x-filament::button
                color="gray"
                :icon="\Filament\Support\Icons\Heroicon::Cog"
                labeled-from="sm"
                tag="button"
            >
              Settings
            </x-filament::button>
                    <form
            action="{{ filament()->getLogoutUrl() }}"
            method="post"
            class="fi-account-widget-logout-form"
        >
            @csrf

            <x-filament::button
                color="gray"
                :icon="\Filament\Support\Icons\Heroicon::ArrowLeftEndOnRectangle"
                :icon-alias="\Filament\View\PanelsIconAlias::WIDGETS_ACCOUNT_LOGOUT_BUTTON"
                labeled-from="sm"
                tag="button"
                type="submit"
            >
           
            </x-filament::button>
        </form>

    </x-filament::section>
</x-filament-widgets::widget>
