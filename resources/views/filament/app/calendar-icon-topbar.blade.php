{{-- Placeholder view to avoid missing view exceptions from FilamentBookingPlugin --}}
<div class="filament-calendar-topbar-icon" style="display:none;"></div>
@php
$isSidebarCollapsibleOnDesktop = filament()->isSidebarCollapsibleOnDesktop();
$anderia = \Andreia\FilamentUiSwitcher\Support\UiPreferenceManager::get('ui.layout', 'sidebar');
$aSiderbar = $anderia === 'sidebar-no-topbar' ? true : false;
@endphp
<div class="fi-no-database" x-data>
<div>
<div class="fi-modal-trigger">
<button
    type="button"
    color="gray"
    icon="heroicon-o-clipboard-document-list"
    icon-size="lg"
    label="Anteckning"
    @if($anderia === 'sidebar-no-topbar')
    class="fi-sidebar-database-notifications-btn"
    @else
    class="fi-icon-btn fi-size-md fi-topbar-database-notifications-btn"
    @endif
    onclick="(function(){
        try { Livewire.dispatch('open-modal', { id: 'user-notes-modal' }); } catch(e) { console.debug('dispatch failed', e); }
        // retry once after a short delay in case of race conditions
        setTimeout(function(){
            try { Livewire.dispatch('open-modal', { id: 'user-notes-modal' }); } catch(e){}
        }, 120);
    })()"
>

        <x-filament::icon
            icon="heroicon-o-clipboard-document-list"
            class="fi-icon fi-size-lg"
        />
@php
// dd($anderia);
@endphp

  @if($anderia === 'sidebar-no-topbar')



    <span

            x-show="$store.sidebar.isOpen"
            x-transition:enter="fi-transition-enter"
            x-transition:enter-start="fi-transition-enter-start"
            x-transition:enter-end="fi-transition-enter-end"

        class="fi-sidebar-database-notifications-btn-label"
    >
    Anteckning..
    </span>
@endif

</button>
 </div>
</div>
</div>

{{-- Modal moved to BODY_START render hook to avoid Livewire entangle conflicts --}}

<script>
// Patch Livewire modal calls to avoid `showModal` DOMException when a non-modal dialog is open.
document.addEventListener('livewire:load', function () {
    if (!window.Livewire) return;

    ['showHtmlModal', 'showFailureModal'].forEach(function (fn) {
        if (typeof Livewire[fn] !== 'function') return;

        const original = Livewire[fn].bind(Livewire);

        Livewire[fn] = function () {
            try {
                // Close any non-modal <dialog open> that may block showModal()
                document.querySelectorAll('dialog[open]').forEach(function (d) {
                    try {
                        // Filament/other libs may mark true modals with data-modal or aria-modal
                        if (!d.hasAttribute('data-modal') && d.getAttribute('aria-modal') !== 'true') {
                            d.close();
                        }
                    } catch (err) {
                        // ignore
                    }
                });

                return original.apply(null, arguments);
            } catch (err) {
                if (err instanceof DOMException) {
                    console.warn('Livewire: suppressed showModal DOMException', err);
                    return;
                }
                throw err;
            }
        };
    });
});
</script>
