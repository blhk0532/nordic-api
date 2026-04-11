{{-- Interactive Controls Bar (only visible during interactive mode) --}}
<div
    x-show="isInteractive"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"
    class="flex items-center justify-center gap-2 px-3 py-2 bg-slate-200/80 dark:bg-black/30 border-t border-slate-300 dark:border-white/5"
>
    <span class="text-[10px] text-slate-500 dark:text-gray-500 uppercase tracking-wide mr-2">Keys:</span>

    {{-- Arrow Keys --}}
    <div class="flex items-center gap-1">
        <button
            type="button"
            wire:click="sendSpecialKey('up')"
            class="flex items-center justify-center w-7 h-7 text-xs font-medium text-slate-600 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded hover:bg-slate-100 dark:hover:bg-zinc-700 hover:border-slate-400 dark:hover:border-zinc-600 transition-colors"
            title="Arrow Up"
        >↑</button>
        <button
            type="button"
            wire:click="sendSpecialKey('down')"
            class="flex items-center justify-center w-7 h-7 text-xs font-medium text-slate-600 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded hover:bg-slate-100 dark:hover:bg-zinc-700 hover:border-slate-400 dark:hover:border-zinc-600 transition-colors"
            title="Arrow Down"
        >↓</button>
        <button
            type="button"
            wire:click="sendSpecialKey('left')"
            class="flex items-center justify-center w-7 h-7 text-xs font-medium text-slate-600 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded hover:bg-slate-100 dark:hover:bg-zinc-700 hover:border-slate-400 dark:hover:border-zinc-600 transition-colors"
            title="Arrow Left"
        >←</button>
        <button
            type="button"
            wire:click="sendSpecialKey('right')"
            class="flex items-center justify-center w-7 h-7 text-xs font-medium text-slate-600 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded hover:bg-slate-100 dark:hover:bg-zinc-700 hover:border-slate-400 dark:hover:border-zinc-600 transition-colors"
            title="Arrow Right"
        >→</button>
    </div>

    <div class="w-px h-5 bg-slate-300 dark:bg-zinc-700 mx-1"></div>

    {{-- Action Keys --}}
    <div class="flex items-center gap-1">
        <button
            type="button"
            wire:click="sendSpecialKey('enter')"
            class="flex items-center justify-center px-2.5 h-7 text-[10px] font-medium text-emerald-600 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/30 border border-emerald-300 dark:border-emerald-700/50 rounded hover:bg-emerald-200 dark:hover:bg-emerald-900/50 hover:border-emerald-400 dark:hover:border-emerald-600/50 transition-colors"
            title="Enter"
        >Enter</button>
        <button
            type="button"
            wire:click="sendSpecialKey('space')"
            class="flex items-center justify-center px-2.5 h-7 text-[10px] font-medium text-blue-600 dark:text-blue-300 bg-blue-100 dark:bg-blue-900/30 border border-blue-300 dark:border-blue-700/50 rounded hover:bg-blue-200 dark:hover:bg-blue-900/50 hover:border-blue-400 dark:hover:border-blue-600/50 transition-colors"
            title="Space"
        >Space</button>
        <button
            type="button"
            wire:click="sendSpecialKey('tab')"
            class="flex items-center justify-center px-2.5 h-7 text-[10px] font-medium text-slate-600 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded hover:bg-slate-100 dark:hover:bg-zinc-700 hover:border-slate-400 dark:hover:border-zinc-600 transition-colors"
            title="Tab"
        >Tab</button>
    </div>

    <div class="w-px h-5 bg-slate-300 dark:bg-zinc-700 mx-1"></div>

    {{-- Escape/Cancel --}}
    <div class="flex items-center gap-1">
        <button
            type="button"
            wire:click="sendSpecialKey('escape')"
            class="flex items-center justify-center px-2.5 h-7 text-[10px] font-medium text-yellow-600 dark:text-yellow-300 bg-yellow-100 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-700/50 rounded hover:bg-yellow-200 dark:hover:bg-yellow-900/50 hover:border-yellow-400 dark:hover:border-yellow-600/50 transition-colors"
            title="Escape"
        >Esc</button>
        <button
            type="button"
            wire:click="sendSpecialKey('backspace')"
            class="flex items-center justify-center px-2.5 h-7 text-[10px] font-medium text-slate-600 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded hover:bg-slate-100 dark:hover:bg-zinc-700 hover:border-slate-400 dark:hover:border-zinc-600 transition-colors"
            title="Backspace"
        >⌫</button>
    </div>

    <div class="w-px h-5 bg-slate-300 dark:bg-zinc-700 mx-1"></div>

    {{-- Function Keys (commonly used) --}}
    <div class="flex items-center gap-1">
        <button
            type="button"
            wire:click="sendSpecialKey('f1')"
            class="flex items-center justify-center w-7 h-7 text-[10px] font-medium text-slate-500 dark:text-zinc-400 bg-slate-100 dark:bg-zinc-800/50 border border-slate-300/50 dark:border-zinc-700/50 rounded hover:bg-slate-200 dark:hover:bg-zinc-700 hover:border-slate-400 dark:hover:border-zinc-600 transition-colors"
            title="F1 - Help"
        >F1</button>
        <button
            type="button"
            wire:click="sendSpecialKey('f10')"
            class="flex items-center justify-center w-8 h-7 text-[10px] font-medium text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/20 border border-red-300/50 dark:border-red-700/30 rounded hover:bg-red-200 dark:hover:bg-red-900/40 hover:border-red-400 dark:hover:border-red-600/50 transition-colors"
            title="F10 - Quit (htop)"
        >F10</button>
    </div>
</div>
