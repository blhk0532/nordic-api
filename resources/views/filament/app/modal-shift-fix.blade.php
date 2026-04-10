<script>
(() => {
    const html = document.documentElement;

    const update = () => {
        // Check if any modal is visible on the screen
        const modals = document.querySelectorAll('.fi-modal-window');
        const hasVisibleModal = Array.from(modals).some(modal => {
            const style = window.getComputedStyle(modal);
            return style.display !== 'none' && style.visibility !== 'hidden';
        });
        html.classList.toggle('fi-modal-open', hasVisibleModal);
    };

    // Watch for DOM changes
    const observer = new MutationObserver(update);
    observer.observe(document.body, { childList: true, subtree: true });

    // Also watch for style changes
    const styleObserver = new MutationObserver(update);
    styleObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['style', 'class'] });

    // Initial check
    update();

    // Periodic check as fallback
    setInterval(update, 100);
})();
</script>
