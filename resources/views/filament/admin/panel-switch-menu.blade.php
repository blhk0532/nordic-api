@if ($isSimple)
    <x-filament::dropdown teleport placement="bottom-end">
        <x-slot name="trigger">
            <button type="button"
                class="fi-icon-btn fi-size-md fi-topbar-database-notifications-btn fi-bg-primary-600 hover:fi-bg-primary-700 focus:fi-ring-primary-500 fi-text-white fi-flex fi-items-center fi-gap-2">
                <span class="w-5 h-5 font-semibold bg-white rounded-full shrink-0 text-primary-500">
                    {{str($labels[$currentPanel->getId()] ?? $currentPanel->getId())->substr(0, 1)->upper()}}
                </span>
                <span class="text-white">
                    {{ $labels[$currentPanel->getId()] ?? str($currentPanel->getId())->ucfirst() }}
                </span>

                <x-filament::icon
                    icon="heroicon-m-chevron-down"
                    icon-alias="panels::panel-switch-simple-icon"
                    class="w-5 h-5 text-white ms-auto shrink-0"
                />

            </button>
        </x-slot>

        <x-filament::dropdown.list>
            @foreach ($panels as $id => $url)
                <x-filament::dropdown.list.item
                    :href="$url"
                    :badge="str($labels[$id] ?? $id)->substr(0, 2)->upper()"
                    tag="a"
                >
                {{ $labels[$id] ?? str($id)->ucfirst() }}
                </x-filament::dropdown.list.item>
            @endforeach
        </x-filament::dropdown.list>

    </x-filament::dropdown>
@else

    <style>
        .panel-switch-modal .fi-modal-content {
            align-items: center !important;
            justify-content: center !important;
        }
        .panel-switch-modal .fi-modal {
    right: auto !important;
    left: 0 !important;
    transform: translateX(-100%) !important;
}

.panel-switch-modal.fi-modal-open .fi-modal {
    transform: translateX(0%) !important;
}

    </style>
    <style>
        /* Keep modal content centered */
        .panel-switch-modal .fi-modal-content {
            align-items: center !important;
            justify-content: center !important;
        }

        /* Helper utility retained */
        .fi-modal-slide-over-left {
            --tw-translate-x: -100%;
        }

        /* Strong, RTL-safe left-origin override (catches utility/class and inline overrides) */
        .panel-switch-modal .fi-modal,
        .panel-switch-modal .fi-modal-content,
        .panel-switch-modal .fi-modal-backdrop,
        .fi-modal-window.fi-align-start {
            right: auto !important;
            left: 0 !important;
            transform-origin: left center !important;
            transition: transform .28s cubic-bezier(.2,.8,.2,1) !important;
        }

        /* Hidden/off-screen left */
        .panel-switch-modal .fi-modal,
        .fi-modal-window.fi-align-start {
            transform: translateX(-100%) !important;
        }

        /* Visible/open state */
        .panel-switch-modal.fi-modal-open .fi-modal,
        .fi-modal-open .fi-modal-window.fi-align-start,
        .fi-modal-window.fi-align-start[aria-hidden="false"] {
            transform: translateX(0%) !important;
            left: 0 !important;
            right: auto !important;
        }

        /* Neutralize common right-placement utility classes */
        .fi-modal-window.fi-align-start.right-0,
        .fi-modal-window.fi-align-start.translate-x-full,
        .fi-modal-window.fi-align-start.-translate-x-0,
        .panel-switch-modal .fi-modal.right-0,
        .panel-switch-modal .fi-modal.translate-x-full {
            right: auto !important;
            left: 0 !important;
            transform: translateX(-100%) !important;
        }

        /* RTL safety: still prefer left for this specific modal */
        [dir="rtl"] .panel-switch-modal .fi-modal,
        [dir="rtl"] .fi-modal-window.fi-align-start {
            left: 0 !important;
            right: auto !important;
            transform: translateX(-100%) !important;
        }

        /* Custom CSS for left-sliding phone dialer modal */

.fi-modal.fi-modal-slide-over-left > .fi-modal-window-ctn > .fi-modal-window {
    margin-inline-start: 0;
    margin-inline-end: auto;
}

.fi-modal.fi-modal-slide-over-left > .fi-modal-window-ctn > .fi-modal-window.fi-transition-enter-start,
.fi-modal.fi-modal-slide-over-left > .fi-modal-window-ctn > .fi-modal-window.fi-transition-leave-end {
    --tw-translate-x: -100%;
    translate: var(--tw-translate-x) var(--tw-translate-y);
}

.fi-modal.fi-modal-slide-over-left > .fi-modal-window-ctn > .fi-modal-window.fi-transition-enter-end,
.fi-modal.fi-modal-slide-over-left > .fi-modal-window-ctn > .fi-modal-window.fi-transition-leave-start {
    --tw-translate-x: calc(var(--spacing) * 0);
    translate: var(--tw-translate-x) var(--tw-translate-y);
}

/* RTL support for left-sliding modal */
.fi-modal.fi-modal-slide-over-left > .fi-modal-window-ctn > .fi-modal-window.fi-transition-enter-start:where(:dir(rtl), [dir=rtl], [dir=rtl] *),
.fi-modal.fi-modal-slide-over-left > .fi-modal-window-ctn > .fi-modal-window.fi-transition-leave-end:where(:dir(rtl), [dir=rtl], [dir=rtl] *) {
    --tw-translate-x: 100%;
    translate: var(--tw-translate-x) var(--tw-translate-y);
}



    </style>

    <script>
        /* Robust runtime fix: retries + MutationObserver to catch Filament reflows */
        (function () {
            const SELECTORS = ['.panel-switch-modal .fi-modal', '.panel-switch-modal .fi-modal-window', '#panel-switch', '.fi-modal-window.fi-align-start'];

            function findModal() {
                for (const s of SELECTORS) {
                    const el = document.querySelector(s);
                    if (el) return el;
                }
                return null;
            }

            function applyLeftPlacement(el) {
                if (! el) return false;

                // Defensive: remove utility classes that force right placement
                el.classList.remove('right-0', 'translate-x-full', '-translate-x-0', 'translate-x-0');

                try {
                    el.style.right = 'auto';
                    el.style.left = '0';
                    el.style.transformOrigin = 'left center';
                    el.style.transform = 'translateX(-100%)';
                    // trigger reflow then animate in
                    // eslint-disable-next-line no-unused-expressions
                    el.getBoundingClientRect();
                    el.style.transition = el.style.transition || 'transform .28s cubic-bezier(.2,.8,.2,1)';
                    requestAnimationFrame(() => {
                        el.style.transform = 'translateX(0)';
                    });

                    return true;
                } catch (err) {
                    return false;
                }
            }

            function ensureModalLeft() {
                const modal = findModal();
                if (modal) {
                    return applyLeftPlacement(modal) || false;
                }
                return false;
            }

            window.addEventListener('open-modal', (e) => {
                const id = e?.detail?.id;
                if (id && id !== 'panel-switch') return;

                if (ensureModalLeft()) return;

                let attempts = 0;
                const maxAttempts = 12;
                const interval = setInterval(() => {
                    attempts += 1;
                    if (ensureModalLeft() || attempts >= maxAttempts) {
                        clearInterval(interval);
                    }
                }, 80);

                const observer = new MutationObserver((mutations, obs) => {
                    if (ensureModalLeft()) {
                        obs.disconnect();
                    }
                });

                observer.observe(document.body, { childList: true, subtree: true });

                setTimeout(() => observer.disconnect(), Math.min(maxAttempts * 80, 2000));
            });

            // Ensure on initial load if present
            document.addEventListener('DOMContentLoaded', () => ensureModalLeft());
        })();
    </script>

@php
    $currentPanelLabel = $labels[$currentPanel->getId()] ?? str($currentPanel->getId())->ucfirst();
@endphp

<x-filament::icon-button
    x-data="{}"
    icon="heroicon-o-shield-check"
    icon-alias="panels::panel-switch-modern-icon"
    icon-size="lg"
    @click="$dispatch('open-modal', { id: 'panel-switch' })"
    :label="$heading"
    style="width: 48px;"
   class="shrink-0 grow-0 text-gray-500 hover:text-gray-700 dark:text-gray-200 dark:hover:text-gray-400 mx-1"
/>

<x-filament::modal
    id="panel-switch"
    width="md"
    alignment="start"
    slide-over
    sticky-header
    :heading="$heading"
    teleport="body"
    display-classes="block"
    width="sm"
    class="panel-switch-modal fi-modal-slide-over-left"
>
    <div
        class="panel-switch-grid"
    >
        @foreach ($panels as $id => $url)
            <a
                href="{{ $url }}"
                class="flex flex-col items-center justify-start flex-1 hover:cursor-pointer group panel-switch-card"
            >
                <div
                    @class([
                        'p-2 bg-white rounded-lg shadow-md dark:bg-gray-800 panel-switch-card-section',
                        'group-hover:ring-2 group-hover:ring-primary-600' => $id !== $currentPanel->getId(),
                        'ring-2 ring-primary-600' => $id === $currentPanel->getId(),
                    ])
                >
                    @if ($renderIconAsImage)
                        <img
                            class="rounded-lg panel-switch-card-image w-16 h-16 object-cover"
                            src="{{ $icons[$id] ?? 'https://raw.githubusercontent.com/bezhanSalleh/filament-panel-switch/3.x/art/banner.jpg' }}"
                            alt="Panel Image"
                        >
                    @else
                        @php
                            $iconName = $icons[$id] ?? 'heroicon-s-square-2-stack';
                        @endphp
                        @svg($iconName, 'text-primary-600 panel-switch-card-icon w-12 h-12')
                    @endif
                </div>
                <span
                    @class([
                        'mt-2 text-sm font-medium text-center text-gray-400 dark:text-gray-200 break-words panel-switch-card-title',
                        'text-gray-400 dark:text-gray-200 group-hover:text-primary-600 group-hover:dark:text-primary-400' => $id !== $currentPanel->getId(),
                        'text-primary-600 dark:text-primary-400' => $id === $currentPanel->getId(),
                    ])
                >
                    {{ $labels[$id] ?? str($id)->ucfirst() }}
                </span>
            </a>
        @endforeach
    </div>
</x-filament::modal>
@endif
