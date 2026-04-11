@extends(\Adultdate\Wirechat\Facades\Wirechat::currentPanel()->getLayout())

@section('content')

    <div class="w-full flex min-h-full h-full rounded-lg">
        {{-- Sidebar: Conversations list - hidden on mobile --}}
        <div x-persist="chats" class="hidden md:grid bg-inherit border-r border-[var(--wc-light-border)] dark:border-[var(--wc-dark-border)] dark:bg-inherit relative w-full h-full md:w-[360px] lg:w-[400px] xl:w-[500px] shrink-0 overflow-y-auto">
            <livewire.filament-wirechat.chats :panel="$panel" />
        </div>

        {{-- Main chat area - full width on mobile --}}
        <main class="flex w-full grow h-full min-h-min relative overflow-y-auto" style="contain:content">
            <livewire.filament-wirechat.chat :panel="$panel" conversation="{{request()->conversation}}"/>
        </main>
    </div>
@endsection
