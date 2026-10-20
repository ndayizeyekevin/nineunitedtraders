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
