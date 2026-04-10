<style>
section.fi-section{
   border: 2px solid #80808080;
}
@layer components {
  @supports (color:color-mix(in lab,red,red)) {
    .fi-section:not(.fi-section-not-contained):not(.fi-aside):where(.dark, .dark *) {
      --tw-ring-color: color-mix(in oklab,var(--color-white)0%,transparent);
    }
  }
}
</style>
<div class="w-full">
    <!-- Techniker Filter -->
    <div class="m-2">
        <select wire:model.live="selectedCalendar" class="fi-input block w-full">
            @foreach(\App\Models\BookingCalendar::with('owner')->get() as $calendar)
                <option value="{{ $calendar->id }}">{{ $calendar->owner?->name ?? $calendar->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Calendar -->
    <div class="calendar-widget-wrapper">
        @livewire(\App\Filament\App\Clusters\Services\Resources\Bookings\Widgets\BookingCalendar::class, ['lazy' => false, 'pageFilters' => ['booking_calendars' => $selectedCalendar]])
    </div>
</div>
