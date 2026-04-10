@php
    $options = ['all' => 'Show All'] + $calendars->toArray();
@endphp
<div class="flex flex-col gap-4 md:flex-row md:items-end w-full pb-2">
    {{-- Tekninker --}}
    <div class="flex-1 fi-fo-field-wrp">
        <div class="grid gap-y-2">
            <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 focus-within:ring-2 fi-fo-select bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 focus-within:ring-primary-600 dark:focus-within:ring-primary-500 overflow-hidden">
                <div class="min-w-0 flex-1">
                    <select class="fi-select-input block w-full border-none bg-transparent py-1.5 pl-3 pr-10 text-gray-900 focus:ring-0 sm:text-sm sm:leading-6 dark:text-white" wire:model.live="selectedTechnician">
                        @foreach($options as $id => $name)
                            <option value="{{ $id }}" @if($id === 'all') selected @endif>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Link Button --}}
    <div class="flex-shrink-0 flex items-center justify-end">
        <a href="'https://ndsth.com'/nds/booking/service/bokning" class="fi-btn fi-btn-color-primary relative flex items-center justify-center gap-1 font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-primary fi-size-md fi-btn-size-md gap-x-2 px-3 py-2 text-sm text-white shadow-sm bg-primary-600 hover:bg-primary-500 focus-visible:ring-primary-500/50 sm:whitespace-nowrap w-full md:w-auto">
            <x-filament::icon icon="heroicon-m-calendar-days" class="w-5 h-5 color-primary text-primary" />

        </a>
    </div>
</div>
