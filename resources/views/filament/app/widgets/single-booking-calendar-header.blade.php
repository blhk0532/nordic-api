@php
    $calendars = \App\Models\BookingCalendar::all()->pluck('name', 'id');
    $options = ['all' => 'Alla tekniker'] + $calendars->toArray();
    $segments = request()->segments();
    $segments[count($segments) - 1] = 'multi-calendars-3';
    $currentUrl = url()->current();
    $newSegment = 'multi-calendars-3';
    $newUrl = url(implode('/', $segments));
    $newUrlX3 = preg_replace('#/[^/]*$#', '/' . $newSegment, $currentUrl);
@endphp

<div class="flex flex-wrap gap-4 items-end w-full pb-2">
    {{-- Tekninker --}}
    <div class="fi-fo-field-wrp w-full flex-shrink-0 sm:flex-[2] sm:min-w-0">
        <div class="grid gap-y-2">
            <div class="flex items-center gap-x-3 justify-between">
                <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3 text-sm font-medium leading-6 text-gray-950 dark:text-white">
                    Tekninker
                </label>
            </div>
            <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 focus-within:ring-2 fi-fo-select bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 focus-within:ring-primary-600 dark:focus-within:ring-primary-500 overflow-hidden">
                <div class="min-w-0 flex-1">
                    <select wire:model.live="selectedTechnician" wire:key="tech-select-{{ (string)$selectedTechnician }}" class="fi-select-input block w-full border-none bg-transparent py-1.5 pl-3 pr-10 text-gray-900 focus:ring-0 sm:text-sm sm:leading-6 dark:text-white">
                        @foreach($options as $id => $name)
                            @php
                                $optionId = (string)$id;
                                $selectedId = (string)$selectedTechnician;
                                $isSelected = $optionId === $selectedId || 
                                            ($optionId !== 'all' && $selectedId !== 'all' && (int)$optionId === (int)$selectedId);
                            @endphp
                            <option value="{{ $optionId }}" @selected($isSelected)>{{ $name }}</option>
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
                    <input wire:model.live="startDate" class="fi-input block w-full border-none bg-transparent py-1.5 px-3 text-gray-900 focus:ring-0 sm:text-sm sm:leading-6 dark:text-white" type="date" />
                </div>
            </div>
        </div>
    </div>

    {{-- End Date --}}
    <div class="fi-fo-field-wrp w-full flex-shrink-0 sm:flex-1 sm:min-w-0">
        <div class="grid gap-y-2">
            <div class="hidden flex items-center gap-x-3 justify-between">
                <label class="hidden fi-fo-field-wrp-label inline-flex items-center gap-x-3 text-sm font-medium leading-6 text-gray-950 dark:text-white">
                    End date
                </label>
            </div>
            <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 focus-within:ring-2 bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 focus-within:ring-primary-600 dark:focus-within:ring-primary-500 overflow-hidden">
                <div class="min-w-0 flex-1">
                    <input wire:model.live="endDate" class="fi-input block w-full border-none bg-transparent py-1.5 px-3 text-gray-900 focus:ring-0 sm:text-sm sm:leading-6 dark:text-white" type="date" />
                </div>
            </div>
        </div>
    </div>


</div>
