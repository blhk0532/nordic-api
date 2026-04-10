<x-filament-widgets::widget>
    <div id="app-wirechat-widget" style="min-height: calc(100vh - 4rem); max-height: calc(100vh - 4rem); height: calc(100vh - 4rem); overflow: hidden;">
        <livewire:filament-wirechat.widget :panel="\Filament\Facades\Filament::getCurrentPanel()?->getId()" />
    </div>
    <style>
        /* Hide empty schema component elements that create gaps */
        [x-data*="filamentSchemaComponent"]:empty {
            display: none !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            min-height: 0 !important;
        }

        /* Target elements with empty path in x-data */
        [x-data*="path: ''"]:empty {
            display: none !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Remove gap between widgets on dashboard - target footer widgets container */
        .fi-page-footer-widgets > div:last-child,
        .fi-page-footer-widgets > [wire\:id*="wirechat-widget"] {
            margin-top: -1.5rem !important;
        }

    .fi-main.fi-width-full {
        padding: 0rem !important;
    }
    .fi-page-header-main-ctn {
        padding: 0rem !important;
    }
    </style>
    <script>
        // Remove empty schema component elements that create gaps
        (function() {
            function removeEmptySchemaComponents() {
                document.querySelectorAll('[x-data*="filamentSchemaComponent"][x-data*="path: \'\'"]').forEach((el) => {
                    if (!el.textContent.trim() && el.children.length === 0) {
                        el.remove();
                    }
                });
            }

            // Run on page load
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', removeEmptySchemaComponents);
            } else {
                removeEmptySchemaComponents();
            }

            // Run after Livewire updates
            document.addEventListener('livewire:init', () => {
                Livewire.hook('morph', () => {
                    setTimeout(removeEmptySchemaComponents, 50);
                });
            });
        })();
    </script>
</x-filament-widgets::widget>
