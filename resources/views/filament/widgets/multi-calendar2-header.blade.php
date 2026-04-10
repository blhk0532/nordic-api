@php
    $options = ['all' => 'Show All'] + $calendars->toArray();
    // Get tenant from current URL by replacing the page part
    $currentUrl = request()->url();
    $singleCalendarUrl = preg_replace('/\/service\/[^\/]+$/', '/single-calendar', $currentUrl);
    $targetCalendar = $selectedTechnician2 ?? null;
    $singleCalendarHref = $singleCalendarUrl . ($targetCalendar && $targetCalendar !== 'all' ? '?booking_calendars=' . urlencode($targetCalendar) : '');
@endphp
<style>
div.filament-fullcalendar.calendar-2 {
 height: 666px;

}
.fc-timegrid-slot-label-cushion.fc-scrollgrid-shrink-cushion{
     height:24px!important;
}
div.fc-timegrid-slot-label-frame.fc-scrollgrid-shrink-frame{
  height:24px!important;
}
</style>
<div class="flex flex-col gap-4 md:flex-row md:items-end w-full pb-2" style="display: contents;">
    {{-- Tekninker --}}
      <div class="flex-1 fi-fo-field-wrp" style="margin-right: 12px;">
        <div class="grid gap-y-2">
            <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 focus-within:ring-2 fi-fo-select bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 focus-within:ring-primary-600 dark:focus-within:ring-primary-500 overflow-hidden">
                <div class="min-w-0 flex-1">
                    <select id="selectedTechnician2" class="fi-select-input block w-full border-none bg-transparent py-1.5 pl-3 pr-10 text-gray-900 focus:ring-0 sm:text-sm sm:leading-6 dark:text-white" wire:model.live="selectedTechnician2">
                        @foreach($options as $id => $name)
                            <option value="{{ $id }}" @if($id == $selectedTechnician2) selected @endif>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Link Button --}}
    <div class="ml-2 flex-shrink-0 flex items-center justify-end">
        <a href="#"
           onclick="(function(){var sel=document.getElementById('selectedTechnician2');var val=sel?sel.value:null;var pathname=window.location.pathname;var m=pathname.match(/^(.+\/team\/[^\/]+)/);var path=m? m[1] + '/single-calendar' : pathname.replace(/\/service\/[^\/]+$/, '/single-calendar');if(val&&val!=='all'){path += '?booking_calendars=' + encodeURIComponent(val);}window.location.href=path;})(); return false;"
           class="fi-btn fi-btn-color-primary relative flex items-center justify-center gap-1 font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-primary fi-size-md fi-btn-size-md gap-x-2 px-3 py-2 text-sm text-white shadow-sm bg-primary-600 hover:bg-primary-500 focus-visible:ring-primary-500/50 sm:whitespace-nowrap w-full md:w-auto">
            <x-filament::icon icon="heroicon-m-calendar-days" class="w-5 h-5 color-primary text-primary"  style="fill:#ffffff;"/>

        </a>
    </div>
</div>
