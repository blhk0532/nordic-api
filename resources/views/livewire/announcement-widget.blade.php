<div>
    @if(count($announcements) > 0)
        @foreach($announcements as $announcement)
            <div class="p-4 bg-gray-100 rounded-md mb-2">
                <h3 class="font-bold">{{ $announcement['title'] }}</h3>
                <p class="text-gray-600">{!! $announcement['content'] !!}</p>
                <p class="text-xs text-gray-500">
                    From: {{ \Carbon\Carbon::parse($announcement['starts_at'])->format('Y-m-d H:i') }}
                    @if($announcement['ends_at'])
                        To: {{ \Carbon\Carbon::parse($announcement['ends_at'])->format('Y-m-d H:i') }}
                    @endif
                </p>
            </div>
        @endforeach
    @else
        <p class="text-gray-500">No active announcements.</p>
    @endif
</div>
