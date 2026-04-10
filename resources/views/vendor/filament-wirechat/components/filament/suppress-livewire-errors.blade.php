<script>
    (function() {
        const originalError = console.error;
        const originalOnError = window.onerror;
        
        window.onerror = function(msg, url, line, col, error) {
            if (msg && typeof msg === "string" && (msg.includes("Snapshot missing") || msg.includes("Could not find Livewire component"))) {
                return true;
            }
            if (originalOnError) return originalOnError.apply(this, arguments);
            return false;
        };
        
        window.addEventListener("unhandledrejection", function(event) {
            const msg = event.reason ? String(event.reason) : "";
            if (msg.includes("Snapshot missing") || msg.includes("Could not find Livewire component")) {
                event.preventDefault();
                return;
            }
        });
        
        console.error = function() {
            const args = Array.prototype.slice.call(arguments);
            const msg = String(args[0] || "");
            if (msg.includes("Snapshot missing") || msg.includes("Could not find Livewire component")) {
                return;
            }
            originalError.apply(console, args);
        };
        
        if (window.Livewire) {
            Livewire.hook("morph.updated", function() {});
        }
        
        document.addEventListener("livewire:init", function() {
            Livewire.hook("morph.updated", function() {});
        });
    })();
</script>
