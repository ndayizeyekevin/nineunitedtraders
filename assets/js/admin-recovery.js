document.querySelectorAll('[data-recovery-form]').forEach((form) => {
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const submitButton = form.querySelector('button[type="submit"]');
    const submitLabel = submitButton?.querySelector('[data-submit-label]');
    const originalLabel = submitLabel?.textContent ?? '';
    const isResetForm = form.dataset.recoveryForm === 'reset';

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!(submitButton instanceof HTMLButtonElement) || !(submitLabel instanceof HTMLElement)) {
            return;
        }

        submitButton.disabled = true;
        submitButton.setAttribute('aria-busy', 'true');
        submitLabel.innerHTML = `${isResetForm ? 'Updating' : 'Sending'} <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>`;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            let result;
            try {
                result = await response.json();
            } catch {
                throw new Error('Your request could not be completed. Please try again.');
            }
            submitLabel.textContent = originalLabel;
            submitButton.removeAttribute('aria-busy');

            if (!response.ok || result.success !== true || typeof result.message !== 'string') {
                throw new Error(typeof result.message === 'string'
                    ? result.message
                    : 'Your request could not be completed. Please try again.');
            }

            if (typeof window.Swal?.fire !== 'function') {
                throw new Error('The confirmation dialog could not be loaded. Please reload the page and try again.');
            }

            const alertResult = await window.Swal.fire({
                icon: 'success',
                title: isResetForm ? 'Password updated' : 'Check your email',
                text: result.message,
                confirmButtonText: isResetForm ? 'Continue to sign in' : 'OK',
                confirmButtonColor: '#1769aa',
                allowOutsideClick: false,
            });

            if (isResetForm && alertResult.isConfirmed) {
                window.location.assign(new URL('admin/login?reset=1', document.baseURI).href);
                return;
            }

            if (!isResetForm) {
                form.reset();
            }
        } catch (error) {
            submitLabel.textContent = originalLabel;
            submitButton.removeAttribute('aria-busy');
            if (typeof window.Swal?.fire === 'function') {
                await window.Swal.fire({
                    icon: 'error',
                    title: 'Unable to complete request',
                    text: error instanceof Error ? error.message : 'Please check your connection and try again.',
                    confirmButtonColor: '#1769aa',
                });
            } else {
                window.alert(error instanceof Error ? error.message : 'Please check your connection and try again.');
            }
        } finally {
            submitButton.disabled = false;
            submitButton.removeAttribute('aria-busy');
            submitLabel.textContent = originalLabel;
        }
    });
});
