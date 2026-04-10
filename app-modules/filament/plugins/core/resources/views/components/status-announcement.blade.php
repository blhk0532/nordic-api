@if (trim((string) $content) !== '')
    <div class="w-full rounded-2xl border border-zinc-200 bg-white/80 p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/40">
        {!! $content !!}
    </div>
@endif
