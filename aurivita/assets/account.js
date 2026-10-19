(() => {
    document.querySelectorAll('.woocommerce-info').forEach((notice) => {
        const action = notice.querySelector('a[href*="wc-resend-set-password"]');
        if (!action || action.parentElement !== notice) return;
        const copy = document.createElement('span');
        copy.className = 'av-account-notice__copy';
        for (const child of Array.from(notice.childNodes)) {
            if (child !== action) copy.append(child);
        }
        notice.classList.add('av-account-notice');
        notice.replaceChildren(copy, action);
    });

    const menu = document.querySelector('.woocommerce-MyAccount-navigation ul');
    const current = menu?.querySelector('a[aria-current="page"]');
    if (!current) return;

    const revealCurrent = () => {
        if (menu.scrollWidth <= menu.clientWidth) return;
        const offset = current.getBoundingClientRect().left - menu.getBoundingClientRect().left + menu.scrollLeft;
        menu.scrollLeft = Math.max(0, offset - (menu.clientWidth - current.offsetWidth) / 2);
    };
    requestAnimationFrame(revealCurrent);
    if ('ResizeObserver' in window) new ResizeObserver(revealCurrent).observe(menu);
    else window.addEventListener('resize', revealCurrent);
})();
