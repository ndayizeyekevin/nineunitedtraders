(() => {
    const toggle = document.querySelector('[data-admin-sidebar-toggle]');
    const closeButton = document.querySelector('[data-admin-sidebar-close]');
    const sidebar = document.getElementById('admin-sidebar');

    if (!toggle || !closeButton || !sidebar) {
        return;
    }

    const setOpen = (open) => {
        document.body.classList.toggle('admin-sidebar-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Close admin navigation' : 'Open admin navigation');
    };

    toggle.addEventListener('click', () => setOpen(!document.body.classList.contains('admin-sidebar-open')));
    closeButton.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });
})();
