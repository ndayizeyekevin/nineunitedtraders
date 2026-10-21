(() => {
    const toggle = document.querySelector('[data-admin-sidebar-toggle]');
    const closeButton = document.querySelector('[data-admin-sidebar-close]');
    const sidebar = document.getElementById('admin-sidebar');

    if (toggle && closeButton && sidebar) {
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
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            const dialog = document.querySelector('.admin-member-dialog[open]');
            if (dialog instanceof HTMLDialogElement) {
                event.preventDefault();
                dialog.close();
            }
        }
    });

    document.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-dialog-open]');
        if (opener) {
            const dialog = document.getElementById(opener.dataset.dialogOpen);
            if (dialog instanceof HTMLDialogElement) {
                dialog.showModal();
                const firstField = dialog.querySelector('input:not([type="hidden"]), textarea');
                if (firstField) {
                    firstField.focus();
                }
            }
            return;
        }

        const closer = event.target.closest('[data-dialog-close]');
        if (closer) {
            const dialog = closer.closest('dialog');
            if (dialog instanceof HTMLDialogElement) {
                dialog.close();
            }
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('[data-swal-confirm]')) {
            return;
        }
        if (form.dataset.confirmed === 'true') {
            delete form.dataset.confirmed;
            return;
        }

        event.preventDefault();
        const options = {
            icon: 'warning',
            title: form.dataset.swalConfirmTitle || 'Are you sure?',
            text: form.dataset.swalConfirm,
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#b42332',
            cancelButtonColor: '#66788a',
            reverseButtons: true,
            customClass: { popup: 'admin-swal-popup' }
        };

        if (!window.Swal) {
            if (window.confirm(options.text)) {
                form.dataset.confirmed = 'true';
                form.requestSubmit();
            }
            return;
        }

        window.Swal.fire(options).then((result) => {
            if (result.isConfirmed) {
                form.dataset.confirmed = 'true';
                form.requestSubmit();
            }
        });
    });

    document.querySelectorAll('.admin-member-dialog').forEach((dialog) => {
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });
})();
