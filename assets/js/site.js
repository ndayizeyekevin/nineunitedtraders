const menuToggle = document.querySelector('.menu-toggle');
const navigation = document.querySelector('.primary-navigation');
const scrollTop = document.querySelector('.scroll-top');
const loginDialog = document.querySelector('#admin-login-dialog');
const loginOpenButton = document.querySelector('[data-login-open]');

document.querySelectorAll('[data-count]').forEach((counter) => {
    const target = Number(counter.dataset.count);
    if (!Number.isFinite(target) || target < 0) {
        return;
    }

    const suffix = counter.dataset.countSuffix || '';
    const showTarget = () => {
        counter.textContent = `${target}${suffix}`;
    };
    const animate = () => {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            showTarget();
            return;
        }

        const duration = 1600;
        const start = performance.now();
        const update = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const easedProgress = 1 - (1 - progress) ** 4;
            counter.textContent = `${Math.round(target * easedProgress)}${suffix}`;

            if (progress < 1) {
                window.requestAnimationFrame(update);
            } else {
                showTarget();
            }
        };

        window.requestAnimationFrame(update);
    };

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                observer.disconnect();
                animate();
            }
        }, { threshold: 0.35 });
        observer.observe(counter);
    } else {
        animate();
    }
});

if (loginDialog instanceof HTMLDialogElement && loginOpenButton instanceof HTMLButtonElement) {
    loginOpenButton.addEventListener('click', () => {
        loginDialog.showModal();
        loginDialog.querySelector('input[name="username"]')?.focus();
    });

    loginDialog.querySelectorAll('[data-login-close]').forEach((button) => {
        button.addEventListener('click', () => loginDialog.close());
    });

    loginDialog.addEventListener('click', (event) => {
        if (event.target === loginDialog) {
            loginDialog.close();
        }
    });
}

const adminLoginForm = document.querySelector('[data-admin-login]');
if (adminLoginForm instanceof HTMLFormElement && loginDialog instanceof HTMLDialogElement) {
    const submitButton = adminLoginForm.querySelector('button[type="submit"]');
    const message = loginDialog.querySelector('.site-login-message');

    adminLoginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!(submitButton instanceof HTMLButtonElement) || !(message instanceof HTMLElement)) {
            return;
        }

        message.hidden = true;
        message.textContent = '';
        submitButton.disabled = true;
        submitButton.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(adminLoginForm.action, {
                method: 'POST',
                body: new FormData(adminLoginForm),
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
                throw new Error('Sign-in could not be completed. Please try again.');
            }

            if (response.ok && result.success === true && typeof result.redirect === 'string') {
                window.location.assign(result.redirect);
                return;
            }

            const errors = Array.isArray(result.errors) ? result.errors.filter((error) => typeof error === 'string') : [];
            message.textContent = errors.length > 0
                ? errors.join(' ')
                : 'Sign-in could not be completed. Please check your details and try again.';
            message.hidden = false;
        } catch (error) {
            message.textContent = error instanceof Error
                ? error.message
                : 'Sign-in could not be completed. Check your connection and try again.';
            message.hidden = false;
        } finally {
            submitButton.disabled = false;
            submitButton.removeAttribute('aria-busy');
        }
    });
}

if (menuToggle && navigation) {
    menuToggle.addEventListener('click', () => {
        const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
        menuToggle.setAttribute('aria-expanded', String(!isExpanded));
        navigation.classList.toggle('is-open', !isExpanded);
    });

    navigation.addEventListener('click', (event) => {
        if (event.target instanceof HTMLAnchorElement && window.matchMedia('(max-width: 860px)').matches) {
            menuToggle.setAttribute('aria-expanded', 'false');
            navigation.classList.remove('is-open');
        }
    });
}

if (scrollTop) {
    const updateScrollTopVisibility = () => {
        scrollTop.hidden = window.scrollY < 320;
    };

    window.addEventListener('scroll', updateScrollTopVisibility, { passive: true });
    updateScrollTopVisibility();
}

document.querySelectorAll('[data-shipment-planner]').forEach((planner) => {
    const serviceSelect = planner.querySelector('[data-planner-service]');
    const guidance = planner.querySelector('[data-planner-guidance]');

    if (!(serviceSelect instanceof HTMLSelectElement) || !guidance) {
        return;
    }

    const updateGuidance = () => {
        const selectedOption = serviceSelect.selectedOptions[0];
        guidance.textContent = selectedOption?.dataset.guidance || '';
    };

    serviceSelect.addEventListener('change', updateGuidance);
    updateGuidance();
});

const quoteServiceSelect = document.querySelector('#service');
if (quoteServiceSelect instanceof HTMLSelectElement) {
    const routeServices = new Set([
        'Cargo transportation',
        'Cement and commodity importation',
        'Clearing agency',
    ]);
    const routeFields = [...document.querySelectorAll('[data-route-field]')];

    const updateRouteRequirements = () => {
        const required = routeServices.has(quoteServiceSelect.value);
        routeFields.forEach((field) => {
            field.required = required;
            const fieldContainer = field.closest('.field');
            const requiredMarker = fieldContainer?.querySelector('[data-route-required-marker]');
            const optionalMarker = fieldContainer?.querySelector('[data-route-optional-marker]');
            if (requiredMarker) {
                requiredMarker.hidden = !required;
            }
            if (optionalMarker) {
                optionalMarker.hidden = required;
            }
        });
    };

    quoteServiceSelect.addEventListener('change', updateRouteRequirements);
    updateRouteRequirements();
}

const quoteForm = document.querySelector('[data-quote-form]');
if (quoteForm instanceof HTMLFormElement && window.location.hash === '#quote-form') {
    const requiredFields = [...quoteForm.querySelectorAll('input[required], select[required], textarea[required]')];

    const updateMissingFieldState = (field) => {
        const isMissing = field.required && !field.validity.valid;
        const fieldContainer = field.closest('.field');
        if (fieldContainer) {
            fieldContainer.classList.toggle('has-missing-required', isMissing);
        }
        field.setAttribute('aria-invalid', String(isMissing));
        return isMissing;
    };

    requiredFields.forEach((field) => {
        updateMissingFieldState(field);
        field.addEventListener('input', () => updateMissingFieldState(field));
        field.addEventListener('change', () => updateMissingFieldState(field));
    });

    const firstMissingField = requiredFields.find((field) => !field.validity.valid);
    if (firstMissingField instanceof HTMLElement) {
        window.requestAnimationFrame(() => firstMissingField.focus({ preventScroll: true }));
    }
}

document.querySelectorAll('[data-hero-slider]').forEach((slider) => {
    const slides = [...slider.querySelectorAll('.hero-slide')];
    const indicators = [...slider.querySelectorAll('[data-hero-slide]')];
    const previousButton = slider.querySelector('[data-hero-previous]');
    const nextButton = slider.querySelector('[data-hero-next]');
    const toggleButton = slider.querySelector('[data-hero-toggle]');

    if (slides.length < 2 || indicators.length !== slides.length || !previousButton || !nextButton || !toggleButton) {
        return;
    }

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let activeIndex = slides.findIndex((slide) => slide.classList.contains('is-active'));
    let userPaused = reduceMotion.matches;
    let timer;

    if (activeIndex < 0) {
        activeIndex = 0;
    }
    toggleButton.hidden = reduceMotion.matches;
    toggleButton.setAttribute('aria-pressed', String(userPaused));
    toggleButton.setAttribute('aria-label', userPaused ? 'Play automatic image rotation' : 'Pause automatic image rotation');
    if (userPaused) {
        toggleButton.innerHTML = '<i class="fa-solid fa-play" aria-hidden="true"></i>';
    }

    const showSlide = (index) => {
        activeIndex = (index + slides.length) % slides.length;
        slides.forEach((slide, slideIndex) => {
            const isActive = slideIndex === activeIndex;
            slide.classList.toggle('is-active', isActive);
            slide.setAttribute('aria-hidden', String(!isActive));
            indicators[slideIndex].classList.toggle('is-active', isActive);
            indicators[slideIndex].setAttribute('aria-pressed', String(isActive));
        });
    };

    const stopTimer = () => {
        window.clearInterval(timer);
    };

    const startTimer = () => {
        stopTimer();
        if (!userPaused && !reduceMotion.matches && !document.hidden
            && !slider.matches(':hover') && !slider.contains(document.activeElement)) {
            timer = window.setInterval(() => showSlide(activeIndex + 1), 3000);
        }
    };

    previousButton.addEventListener('click', () => {
        showSlide(activeIndex - 1);
        startTimer();
    });
    nextButton.addEventListener('click', () => {
        showSlide(activeIndex + 1);
        startTimer();
    });
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            showSlide(index);
            startTimer();
        });
    });
    toggleButton.addEventListener('click', () => {
        userPaused = !userPaused;
        toggleButton.setAttribute('aria-pressed', String(userPaused));
        toggleButton.setAttribute('aria-label', userPaused ? 'Play automatic image rotation' : 'Pause automatic image rotation');
        toggleButton.innerHTML = `<i class="fa-solid ${userPaused ? 'fa-play' : 'fa-pause'}" aria-hidden="true"></i>`;
        startTimer();
    });
    slider.addEventListener('mouseenter', stopTimer);
    slider.addEventListener('mouseleave', startTimer);
    slider.addEventListener('focusin', stopTimer);
    slider.addEventListener('focusout', (event) => {
        if (!slider.contains(event.relatedTarget)) {
            startTimer();
        }
    });
    document.addEventListener('visibilitychange', startTimer);
    reduceMotion.addEventListener('change', (event) => {
        userPaused = event.matches;
        toggleButton.hidden = event.matches;
        toggleButton.setAttribute('aria-pressed', String(userPaused));
        toggleButton.setAttribute('aria-label', userPaused ? 'Play automatic image rotation' : 'Pause automatic image rotation');
        toggleButton.innerHTML = `<i class="fa-solid ${userPaused ? 'fa-play' : 'fa-pause'}" aria-hidden="true"></i>`;
        startTimer();
    });

    showSlide(activeIndex);
    startTimer();
});

document.querySelectorAll('[data-contact-form]').forEach((form) => {
    const submitButton = form.querySelector('button[type="submit"]');
    const errorMessage = form.querySelector('[data-form-error]');
    const successMessage = form.querySelector('[data-form-success]');
    const phoneInput = form.querySelector('[name="phone"]');
    const emailInput = form.querySelector('[name="email"]');
    const countryDial = form.querySelector('[name="country_dial"]');

    if (!(form instanceof HTMLFormElement) || !submitButton || !errorMessage || !successMessage) {
        return;
    }

    if (phoneInput instanceof HTMLInputElement && emailInput instanceof HTMLInputElement) {
        const clearContactValidation = () => phoneInput.setCustomValidity('');
        phoneInput.addEventListener('input', clearContactValidation);
        emailInput.addEventListener('input', clearContactValidation);
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        errorMessage.hidden = true;
        successMessage.hidden = true;

        if (phoneInput instanceof HTMLInputElement && emailInput instanceof HTMLInputElement) {
            const phone = phoneInput.value.trim();
            phoneInput.setCustomValidity('');

            if (phone === '' && emailInput.value.trim() === '') {
                phoneInput.setCustomValidity('Enter a phone number or email address so we can reply.');
            } else if (phone !== '' && !/^[0-9+\s().-]{7,40}$/.test(phone)) {
                phoneInput.setCustomValidity('Enter a valid phone number.');
            } else if (phone !== '' && countryDial instanceof HTMLInputElement && countryDial.value === '') {
                phoneInput.setCustomValidity('Choose a country with a calling code.');
            }
        }

        if (!form.reportValidity()) {
            return;
        }

        const endpoint = form.dataset.ajaxEndpoint;
        if (!endpoint) {
            errorMessage.textContent = 'The contact form is not configured. Please contact us by phone or email.';
            errorMessage.hidden = false;
            return;
        }

        submitButton.disabled = true;
        submitButton.setAttribute('aria-busy', 'true');
        submitButton.dataset.originalText = submitButton.textContent.trim();
        submitButton.innerHTML = 'Sending… <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>';

        try {
            const formData = new FormData(form);
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(Object.fromEntries(formData.entries())),
            });
            const result = await response.json();

            if (!response.ok || ![true, 'true'].includes(result.success)) {
                throw new Error(typeof result.message === 'string' ? result.message : 'We could not send your inquiry. Please try again or contact us directly.');
            }

            form.reset();
            form.querySelectorAll('[data-country-picker]').forEach((picker) => {
                const countryCode = picker.querySelector('[data-country-value]')?.value;
                const defaultCountry = picker.querySelector(`[data-country-code="${countryCode}"]`);
                defaultCountry?.click();
            });
            successMessage.textContent = 'Thank you. Your inquiry has been sent to our team. We will get back to you as soon as possible.';
            successMessage.hidden = false;
        } catch (error) {
            errorMessage.textContent = error instanceof Error
                ? error.message
                : 'We could not send your inquiry. Please try again or contact us directly.';
            errorMessage.hidden = false;
        } finally {
            submitButton.disabled = false;
            submitButton.removeAttribute('aria-busy');
            submitButton.innerHTML = `${submitButton.dataset.originalText || 'Send inquiry'} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>`;
        }
    });
});

document.querySelectorAll('[data-country-picker]').forEach((picker) => {
    const button = picker.querySelector('[data-country-button]');
    const menu = picker.querySelector('[data-country-menu]');
    const search = picker.querySelector('[data-country-search]');
    const hiddenValue = picker.querySelector('[data-country-value]');
    const hiddenDial = picker.querySelector('[data-country-dial-value]');
    const flag = picker.querySelector('[data-country-flag]');
    const dial = picker.querySelector('[data-country-dial]');
    const emptyMessage = picker.querySelector('[data-country-empty]');
    const options = [...picker.querySelectorAll('[data-country-option]')];

    if (!button || !menu || !search || !hiddenValue || !hiddenDial || !flag || !dial || !emptyMessage) {
        return;
    }

    const closeMenu = () => {
        menu.hidden = true;
        button.setAttribute('aria-expanded', 'false');
        picker.classList.remove('is-open');
    };

    const openMenu = () => {
        menu.hidden = false;
        button.setAttribute('aria-expanded', 'true');
        picker.classList.add('is-open');
        search.value = '';
        options.forEach((option) => {
            option.hidden = false;
        });
        emptyMessage.hidden = true;
        search.focus();
    };

    button.addEventListener('click', () => {
        if (menu.hidden) {
            openMenu();
        } else {
            closeMenu();
        }
    });

    search.addEventListener('input', () => {
        const query = search.value.trim().toLocaleLowerCase();
        let visibleCount = 0;

        options.forEach((option) => {
            const label = `${option.dataset.countryName} ${option.dataset.countryCode} ${option.dataset.countryDial}`.toLocaleLowerCase();
            option.hidden = !label.includes(query);
            if (!option.hidden) {
                visibleCount += 1;
            }
        });

        emptyMessage.hidden = visibleCount !== 0;
    });

    search.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu();
            button.focus();
        } else if (event.key === 'ArrowDown') {
            event.preventDefault();
            options.find((option) => !option.hidden)?.focus();
        }
    });

    options.forEach((option, index) => {
        option.addEventListener('click', () => {
            const code = option.dataset.countryCode;
            const name = option.dataset.countryName;
            const callingCode = option.dataset.countryDial;
            const flagSource = option.dataset.countryFlagSrc;

            if (!code || !name || !callingCode || !flagSource) {
                return;
            }

            hiddenValue.value = code;
            hiddenDial.value = callingCode;
            const contactPhone = picker.closest('[data-contact-form]')?.querySelector('[name="phone"]');
            if (contactPhone instanceof HTMLInputElement) {
                contactPhone.setCustomValidity('');
            }
            flag.src = flagSource;
            flag.alt = '';
            dial.textContent = callingCode || 'N/A';
            button.setAttribute('aria-label', `Phone country: ${name} ${callingCode}`);
            options.forEach((countryOption) => {
                countryOption.setAttribute('aria-selected', String(countryOption === option));
            });
            closeMenu();
            button.focus();
        });

        option.addEventListener('keydown', (event) => {
            const visibleOptions = options.filter((countryOption) => !countryOption.hidden);
            const visibleIndex = visibleOptions.indexOf(option);
            let nextOption;

            if (event.key === 'Escape') {
                closeMenu();
                button.focus();
            } else if (event.key === 'ArrowDown') {
                nextOption = visibleOptions[Math.min(visibleIndex + 1, visibleOptions.length - 1)];
            } else if (event.key === 'ArrowUp') {
                if (visibleIndex === 0) {
                    search.focus();
                } else {
                    nextOption = visibleOptions[visibleIndex - 1];
                }
            } else if (event.key === 'Tab' && index === options.length - 1 && !event.shiftKey) {
                closeMenu();
            }

            if (nextOption) {
                event.preventDefault();
                nextOption.focus();
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (!picker.contains(event.target)) {
            closeMenu();
        }
    });
});
