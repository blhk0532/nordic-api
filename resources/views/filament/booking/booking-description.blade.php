<div>
    <div>{{ $client_name ?? '' }}@if(($client_name ?? '') && ($phone ?? '')) {{ ' ' }}@endif{{ $phone ?? '' }}</div>
    <div>{{ $street ?? '' }}@if(($street ?? '') && ($city ?? '')), @endif{{ $city ?? '' }}</div>
    @if(!empty($services))
        <div class="mt-1 text-sm text-gray-600 space-y-0">
            @foreach($services as $svc)
                <div>{{ $svc }}</div>
            @endforeach
        </div>
    @endif
</div>
