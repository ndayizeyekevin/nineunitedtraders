const menuToggle = document.querySelector('.menu-toggle');
const navigation = document.querySelector('.primary-navigation');
const scrollTop = document.querySelector('.scroll-top');

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

document.querySelectorAll('[data-country-picker]').forEach((picker) => {
    const button = picker.querySelector('[data-country-button]');
    const menu = picker.querySelector('[data-country-menu]');
    const search = picker.querySelector('[data-country-search]');
    const hiddenValue = picker.querySelector('[data-country-value]');
    const flag = picker.querySelector('[data-country-flag]');
    const dial = picker.querySelector('[data-country-dial]');
    const emptyMessage = picker.querySelector('[data-country-empty]');
    const options = [...picker.querySelectorAll('[data-country-option]')];

    if (!button || !menu || !search || !hiddenValue || !flag || !dial || !emptyMessage) {
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
            flag.src = flagSource;
            flag.alt = '';
            dial.textContent = callingCode;
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
