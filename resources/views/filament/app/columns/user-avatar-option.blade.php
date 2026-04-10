<div class="flex items-center gap-2">
    @foreach($users as $user)
        @if($user)
            <div class="flex items-center gap-2">
                <x-filament-panels::avatar.user :user="$user" size="sm" />
                <span class="text-sm font-medium">{{ filament()->getUserName($user) }}</span>
            </div>
        @endif
    @endforeach
</div>
