@php
    $calendars = \App\Models\BookingCalendar::all()->pluck('name', 'id');
    $options = ['all' => 'Show All'] + $calendars->toArray();
@endphp

<div class="flex flex-wrap gap-4 items-end w-full pb-2">
    {{-- Tekninker --}}
    <div class="fi-fo-field-wrp w-full flex-shrink-0 sm:flex-1 sm:min-w-0">
        <div class="grid gap-y-2">
            <div class="flex items-center gap-x-3 justify-between">
                <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3 text-sm font-medium leading-6 text-gray-950 dark:text-white">
                    Tekninker
                </label>
            </div>
            <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 focus-within:ring-2 fi-fo-select bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 focus-within:ring-primary-600 dark:focus-within:ring-primary-500 overflow-hidden">
                <div class="min-w-0 flex-1">
                    <select class="fi-select-input block w-full border-none bg-transparent py-1.5 pl-3 pr-10 text-gray-900 focus:ring-0 sm:text-sm sm:leading-6 dark:text-white" wire:model.live="selectedTechnician">
                        @foreach($options as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Start Date --}}
    <div class="fi-fo-field-wrp w-full flex-shrink-0 sm:flex-1 sm:min-w-0">
        <div class="grid gap-y-2">
            <div class="hidden flex items-center gap-x-3 justify-between">
                <label class="hidden fi-fo-field-wrp-label inline-flex items-center gap-x-3 text-sm font-medium leading-6 text-gray-950 dark:text-white">
                    Start date
                </label>
            </div>
            <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 focus-within:ring-2 bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 focus-within:ring-primary-600 dark:focus-within:ring-primary-500 overflow-hidden">
                <div class="min-w-0 flex-1">
                    <input class="fi-input block w-full border-none bg-transparent py-1.5 px-3 text-gray-900 focus:ring-0 sm:text-sm sm:leading-6 dark:text-white" type="date" wire:model.live="startDate" />
                </div>
            </div>
        </div>
    </div>

    {{-- End Date --}}
    <div class="fi-fo-field-wrp w-full flex-shrink-0 sm:flex-1 sm:min-w-0">
        <div class="grid gap-y-2">
            <div class="hidden flex items-center gap-x-3 justify-between">
                <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3 text-sm font-medium leading-6 text-gray-950 dark:text-white">
                    End date
                </label>
            </div>
            <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 focus-within:ring-2 bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 focus-within:ring-primary-600 dark:focus-within:ring-primary-500 overflow-hidden">
                <div class="min-w-0 flex-1">
                    <input class="fi-input block w-full border-none bg-transparent py-1.5 px-3 text-gray-900 focus:ring-0 sm:text-sm sm:leading-6 dark:text-white" type="date" wire:model.live="endDate" />
                </div>
            </div>
        </div>
    </div>

    {{-- Link Button --}}
    <div class="flex items-center justify-end flex-shrink-0">
        <a href="'https://ndsth.com'/nds/booking/service/bokning" class="fi-btn fi-btn-color-primary relative flex items-center justify-center gap-1 font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-primary fi-size-md fi-btn-size-md gap-x-2 px-3 py-2 text-sm text-white shadow-sm bg-primary-600 hover:bg-primary-500 focus-visible:ring-primary-500/50 sm:whitespace-nowrap w-full sm:w-auto">
            Kalendrar
            <x-filament::icon icon="heroicon-m-calendar-days" class="w-5 h-5 text-white color-white" />
            x 3
        </a>
    </div>
</div>
