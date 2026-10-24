document.querySelectorAll('.password-input').forEach((wrapper) => {
    const password = wrapper.querySelector('input[type="password"]');
    const toggle = wrapper.querySelector('.password-toggle');
    const icon = toggle?.querySelector('i');

    if (!(password instanceof HTMLInputElement) || !(toggle instanceof HTMLButtonElement) || !(icon instanceof HTMLElement)) {
        return;
    }

    wrapper.classList.add('is-enhanced');
    toggle.addEventListener('click', () => {
        const isVisible = password.type === 'text';
        password.type = isVisible ? 'password' : 'text';
        toggle.setAttribute('aria-pressed', String(!isVisible));
        toggle.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
        icon.classList.toggle('fa-eye', isVisible);
        icon.classList.toggle('fa-eye-slash', !isVisible);
    });
});
