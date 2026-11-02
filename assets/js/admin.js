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

    const accountMenu = document.querySelector('[data-admin-account-menu]');
    if (accountMenu instanceof HTMLDetailsElement) {
        document.addEventListener('click', (event) => {
            if (event.target instanceof Node && !accountMenu.contains(event.target)) {
                accountMenu.open = false;
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && accountMenu.open) {
                accountMenu.open = false;
                accountMenu.querySelector('summary')?.focus();
            }
        });
    }

    document.querySelectorAll('[data-password-validation]').forEach((password) => {
        if (!(password instanceof HTMLInputElement)) {
            return;
        }

        const username = document.getElementById(password.dataset.passwordUsername);
        const rulesList = document.getElementById(password.getAttribute('aria-describedby'));
        const confirmation = document.getElementById(password.dataset.passwordConfirmation);
        const matchMessage = confirmation?.closest('.field')?.querySelector('[data-password-match-message]');
        const ruleTests = {
            length: (value) => Array.from(value).length >= 6,
            uppercase: (value) => /[A-Z]/.test(value),
            lowercase: (value) => /[a-z]/.test(value),
            number: (value) => /[0-9]/.test(value),
            username: (value) => !(username instanceof HTMLInputElement)
                || username.value === ''
                || !value.toLowerCase().includes(username.value.toLowerCase()),
            bytes: (value) => new TextEncoder().encode(value).length <= 72,
        };
        const ruleMessages = {
            length: 'Use at least 6 characters.',
            uppercase: 'Add an uppercase letter.',
            lowercase: 'Add a lowercase letter.',
            number: 'Add a number.',
            username: 'Do not include your username.',
            bytes: 'Use no more than 72 bytes.',
        };

        const updatePasswordValidation = () => {
            const value = password.value;
            const isEmpty = value === '';
            const errors = [];

            if (rulesList instanceof HTMLElement) {
                rulesList.hidden = isEmpty;
                rulesList.querySelectorAll('[data-password-rule]').forEach((rule) => {
                    const ruleName = rule.dataset.passwordRule;
                    const isValid = ruleName !== undefined && ruleTests[ruleName]?.(value);
                    rule.classList.toggle('is-valid', Boolean(isValid));
                    const icon = rule.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-circle-check', Boolean(isValid));
                        icon.classList.toggle('fa-circle', !isValid);
                    }
                    if (ruleName && !isValid && ruleMessages[ruleName]) {
                        errors.push(ruleMessages[ruleName]);
                    }
                });
            }

            password.setCustomValidity(isEmpty ? '' : errors.join(' '));

            if (confirmation instanceof HTMLInputElement) {
                const mismatch = confirmation.value !== '' && confirmation.value !== value;
                confirmation.setCustomValidity(mismatch ? 'The new password confirmation does not match.' : '');
                if (matchMessage instanceof HTMLElement) {
                    matchMessage.textContent = confirmation.value === ''
                        ? ''
                        : (mismatch ? 'Passwords do not match.' : 'Passwords match.');
                    matchMessage.classList.toggle('is-valid', confirmation.value !== '' && !mismatch);
                }
            }
        };

        password.addEventListener('input', updatePasswordValidation);
        username?.addEventListener('input', updatePasswordValidation);
        confirmation?.addEventListener('input', updatePasswordValidation);
        updatePasswordValidation();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            if (window.Swal?.isVisible()) {
                return;
            }
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
            target: form.closest('dialog') || document.body,
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
