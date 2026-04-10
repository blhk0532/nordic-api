@php
  //  $isAdmin = (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super') || auth()->user()->hasRole('manager')) ? true : false;
    $isRole = auth()->user()->role;
    $isAdmin = ($isRole == 'admin' || $isRole == 'super' || $isRole == 'manager') ? true : false;
@endphp

<x-filament-widgets::widget
    class="overflow-hidden"
    id="announcement-widget"
    wire:poll.10s="refreshAnnouncements"
>
   @if(count($announcements) > 0)
    <div class="w-full relative bottom-6" style="top:-24px;margin-bottom: -24px;">

            @foreach($announcements as $announcement)
            @if($announcement->starts_at < now() && $announcement->ends_at > now())
             <section class="fi-section  announcement-widget-item" style="padding: 0px;background:#18181b;" id="status-overview-widget-section">
                <div class="bg-transparent p-6 rounded-md mt-6">
                    <div class="flex justify-between items-start relative">

<div class="grid gap-1">
<div class="flex">
                       <p class="text-sm">{{ $announcement->title }}</p>
</div>
<div class="flex">
                     <p class="font-semibold text-xl"> {!! $announcement->content !!} </p>
</div>
</div>


<div class="announcement-dates grid gap-3" style="position: relative;right: 0px;">
<span class="fi-color fi-color-success fi-text-color-700 dark:fi-text-color-300 fi-badge fi-size-md">
    <span class="fi-badge-label-ctn">
        <span class="fi-badge-label">


                            <span class="font-semibold"></span>
                            {{ \Carbon\Carbon::parse($announcement->starts_at)->format('D j M') }}

        </span>
    </span>
</span>

</div>


      @if($isAdmin)

                              @if($announcement->user)
                        <div class="text-xs text-gray-500 mt-2 space-y-1 hidden"
                        style="position: absolute;z-index: 0;right:6px;bottom:-10px;background:none;border:none;padding:0;"

                        >
                            <p
                            ><span class="font-semibold"></span> {{ $announcement->user->name }}</p>
                        </div>
                        @endif

                      <button
                            wire:click="deleteAnnouncement(@js($announcement->id))"
                            type="button"
                            class="text-xs text-gray-500 hover:text-red-500 cursor-pointer"
                            style="z-index:0; position: absolute;z-index: 0;right:4px;top:32px;background:none;border:none;padding:0;"
                            title="Delete announcement"
                        >
<x-filament::icon
    icon="heroicon-o-trash"
    class="w-4 h-4 opacity-60 hover:opacity-80 hover:text-red-500 transition"
/>
                         </button>

                      <button
                            wire:click="editAnnouncement(@js($announcement->id))"
                            type="button"
                            class="text-xs text-gray-500 hover:text-blue-500 cursor-pointer hidden"
                            style="position: absolute;z-index: 0;right:28px;top:32px;background:none;border:none;padding:0;"
                            title="Edit announcement"
                        >
<x-filament::icon
    icon="heroicon-o-pencil-square"
    class="w-4 h-4 opacity-60 hover:opacity-80 hover:text-blue-500 transition"
/>
                         </button>
@endif

                    </div>

                    <div class="text-xs text-gray-500 mt-2 space-y-1">
                        @if($announcement->tekniker)
                            <p><span class="font-semibold">Tekniker:</span> {{ $announcement->tekniker->name }}</p>
                        @endif
                        @if($announcement->component)
                            <p><span class="font-semibold">Tekniker:</span> {{ $announcement->component->name }}</p>
                        @endif

                    </div>
                </div>
                 </section>
                 @endif
            @endforeach
        @else
            <p class="text-gray-500 hidden">No announcements.</p>
        @endif
    </div>

</x-filament-widgets::widget>


