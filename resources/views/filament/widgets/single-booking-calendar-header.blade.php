@php
    $calendars = \App\Models\BookingCalendar::all()->pluck('name', 'id');
    $options = ['all' => 'Show All'] + $calendars->toArray();
    $segments = request()->segments(); // array of URI segments
    $segments[count($segments) - 1] = 'multi-calendars-3';
    $currentUrl = url()->current(); // no query string
    $newSegment = 'multi-calendars-3';
    $newUrl = url(implode('/', $segments));
    $newUrlX3 = preg_replace('#/[^/]*$#', '/' . $newSegment, $currentUrl);
@endphp

<div class="flex flex-wrap gap-4 items-end w-full pb-2">
    {{-- Tekninker --}}
    <div class="fi-fo-field-wrp w-full flex-shrink-0 sm:flex-1 sm:min-w-0">
        <div class="grid gap-y-2">
            <div class="flex items-center gap-x-3 justify-between">
                <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3 text-sm font-medium leading-6 text-gray-950 dark:text-white">

                </label>
            </div>
            <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 focus-within:ring-2 fi-fo-select bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 focus-within:ring-primary-600 dark:focus-within:ring-primary-500 overflow-hidden">
                <div class="min-w-0 flex-1">
                    <select id="single_selected_technician" class="fi-select-input block w-full border-none bg-transparent py-1.5 pl-3 pr-10 text-gray-900 focus:ring-0 sm:text-sm sm:leading-6 dark:text-white" wire:model.live="selectedTechnician">
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
                <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3 text-sm font-medium leading-6 text-gray-950 dark:text-white">

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
                <label class="hidden fi-fo-field-wrp-label inline-flex items-center gap-x-3 text-sm font-medium leading-6 text-gray-950 dark:text-white">

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

<a
    x-data
    :href="(() => {
        const url = new URL(window.location.href)
        const parts = url.pathname.split('/').filter(Boolean)

        parts[parts.length - 1] = 'multi-calendars-2'

        return url.origin + '/' + parts.join('/')
    })()"
    class="fi-btn fi-btn-color-primary relative flex items-center justify-center gap-1 font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-primary fi-size-md fi-btn-size-md gap-x-2 px-3 py-2 text-sm text-white shadow-sm bg-primary-600 hover:bg-primary-500 focus-visible:ring-primary-500/50 sm:whitespace-nowrap w-full sm:w-auto hidden"
    style="display: none;"
    >
    <x-filament::icon icon="heroicon-m-calendar-days" class="w-5 h-5 text-white color-white" />
    2
</a>
<a
    x-data
    :href="(() => {
        const url = new URL(window.location.href)
        const parts = url.pathname.split('/').filter(Boolean)

        parts[parts.length - 1] = 'multi-calendars-3'

        return url.origin + '/' + parts.join('/')
    })()"
    class="fi-btn fi-btn-color-primary relative flex items-center justify-center gap-1 font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-primary fi-size-md fi-btn-size-md gap-x-2 px-3 py-2 text-sm text-white shadow-sm bg-primary-600 hover:bg-primary-500 focus-visible:ring-primary-500/50 sm:whitespace-nowrap w-full sm:w-auto"
>
    <x-filament::icon icon="heroicon-m-calendar-days" class="w-5 h-5 text-white color-white" />
    3
</a>

    </div>

    <script>
        (function(){
            try {
                const params = new URLSearchParams(window.location.search);
                const cal = params.get('booking_calendars');
                if (cal) {
                    const sel = document.getElementById('single_selected_technician');
                    if (sel) {
                        // set value and dispatch change so Livewire updates
                        sel.value = cal;
                        sel.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            } catch (e) {
                // noop
            }
        })();
    </script>

</div>
