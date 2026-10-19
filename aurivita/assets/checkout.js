(() => {
  const dialog = document.getElementById('av-checkout-policy-dialog');
  if (!dialog || typeof dialog.showModal !== 'function') return;
  const title = dialog.querySelector('[data-policy-title]');
  const content = dialog.querySelector('[data-policy-content]');
  const scrollBody = dialog.querySelector('[data-policy-scroll]');
  const closeButton = dialog.querySelector('[data-policy-close]');
  const documents = new Map();
  const pathKey = path => {
    try { return decodeURIComponent(path).replace(/\/+$/, '') || '/'; }
    catch { return path.replace(/\/+$/, '') || '/'; }
  };
  document.querySelectorAll('template[data-av-policy-document]').forEach(template => {
    documents.set(pathKey(template.dataset.policyPath), template);
  });
  let opener;
  let checkoutPosition;
  let closeTimer;
  let closing = false;
  const documentFor = link => {
    try {
      const url = new URL(link.href, location.href);
      return url.origin === location.origin && !url.hash ? documents.get(pathKey(url.pathname)) : null;
    } catch { return null; }
  };
  const decorate = () => {
    document.querySelectorAll('.av-checkout-footer a, .wc-block-checkout__terms a').forEach(link => {
      if (!documentFor(link)) return;
      link.setAttribute('aria-haspopup', 'dialog');
      link.setAttribute('aria-controls', dialog.id);
    });
  };
  const finishClose = () => {
    if (!closing) return;
    clearTimeout(closeTimer);
    dialog.close();
  };
  const close = () => {
    if (!dialog.open || closing) return;
    closing = true;
    dialog.dataset.closing = '';
    dialog.inert = true;
    if (matchMedia('(prefers-reduced-motion: reduce)').matches) finishClose();
    else closeTimer = setTimeout(finishClose, 300);
  };
  const open = (template, link) => {
    clearTimeout(closeTimer);
    closing = false;
    dialog.removeAttribute('data-closing');
    dialog.inert = false;
    if (!dialog.open) {
      opener = link;
      checkoutPosition = { left: window.scrollX, top: window.scrollY, behavior: 'instant' };
    }
    title.textContent = template.dataset.policyTitle;
    content.replaceChildren(template.content.cloneNode(true));
    document.documentElement.classList.add('av-checkout-policy-open');
    document.body.classList.add('av-checkout-policy-open');
    if (!dialog.open) dialog.showModal();
    scrollBody.scrollTop = 0;
    closeButton.focus({ preventScroll: true });
  };
  document.addEventListener('click', event => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    const link = event.target.closest('a');
    if (!link || !link.closest('.av-checkout-footer, .wc-block-checkout__terms, [data-policy-content]') || link.hasAttribute('download')) return;
    if (link.target === '_blank' && link.closest('[data-policy-content]')) return;
    const template = documentFor(link);
    if (!template) return;
    event.preventDefault();
    open(template, link);
  });
  dialog.querySelectorAll('[data-policy-close]').forEach(button => button.addEventListener('click', close));
  dialog.addEventListener('cancel', event => { event.preventDefault(); close(); });
  let backdropPress = false;
  const outside = event => {
    const rect = dialog.getBoundingClientRect();
    return event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom;
  };
  dialog.addEventListener('pointerdown', event => { backdropPress = event.target === dialog && outside(event); });
  dialog.addEventListener('click', event => {
    if (backdropPress && event.target === dialog && outside(event)) close();
    backdropPress = false;
  });
  dialog.addEventListener('animationend', event => {
    if (event.target === dialog && !event.pseudoElement && event.animationName === 'av-policy-out') finishClose();
  });
  dialog.addEventListener('close', () => {
    clearTimeout(closeTimer);
    closing = false;
    dialog.removeAttribute('data-closing');
    dialog.inert = false;
    document.documentElement.classList.remove('av-checkout-policy-open');
    document.body.classList.remove('av-checkout-policy-open');
    if (opener?.isConnected) opener.focus({ preventScroll: true });
    if (checkoutPosition) window.scrollTo(checkoutPosition);
  });
  const couponReady = new WeakSet();
  const nativeCoupon = () => {
    if (!matchMedia('(min-width:1040px)').matches) return;
    const root = document.querySelector('.wc-block-checkout:not(.is-loading)');
    const button = root?.querySelector('.wc-block-checkout__sidebar .wc-block-components-totals-coupon .wc-block-components-panel__button');
    if (!button || couponReady.has(button)) return;
    couponReady.add(button);
    if (button.getAttribute('aria-expanded') !== 'true') {
      const previousFocus = document.activeElement;
      button.click();
      requestAnimationFrame(() => requestAnimationFrame(() => {
        const input = button.closest('.wc-block-components-totals-coupon')?.querySelector('input');
        if (!input || document.activeElement !== input) return;
        if (previousFocus?.isConnected && previousFocus !== document.body) previousFocus.focus({ preventScroll: true });
        else input.blur();
      }));
    }
    button.closest('.wc-block-components-totals-coupon').classList.add('av-checkout-coupon-ready');
  };
  decorate();
  nativeCoupon();
  const checkout = document.querySelector('.wp-block-woocommerce-checkout');
  if (checkout) new MutationObserver(() => { decorate(); nativeCoupon(); }).observe(checkout, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] });
  matchMedia('(min-width:1040px)').addEventListener('change', () => {
    const button = document.querySelector('.wc-block-checkout__sidebar .wc-block-components-totals-coupon .wc-block-components-panel__button');
    if (button) couponReady.delete(button);
    nativeCoupon();
  });
})();

(() => {
  const checkout = document.querySelector('.wp-block-woocommerce-checkout');
  if (!checkout) return;
  const expandedAddressControls = new WeakSet();
  const prepareFields = () => {
    checkout.querySelectorAll('.wc-block-checkout__shipping-fields .wc-block-checkout__use-address-for-billing').forEach(wrapper => {
      const checkbox = wrapper.querySelector('input[type="checkbox"]');
      if (!checkbox) return;
      if (!checkbox.checked && !checkbox.disabled && checkbox.getAttribute('aria-disabled') !== 'true' && !checkbox.closest('fieldset:disabled')) checkbox.click();
      wrapper.classList.toggle('av-use-delivery-address', checkbox.checked);
    });
    checkout.querySelectorAll('input[data-av-invoice-field="tax-number"]').forEach(input => {
      if (input.inputMode !== 'numeric') input.inputMode = 'numeric';
    });
    checkout.querySelectorAll('.wc-block-checkout__shipping-fields .wc-block-components-address-form__address_2-toggle,.wc-block-checkout__billing-fields .wc-block-components-address-form__address_2-toggle').forEach(toggle => {
      if (expandedAddressControls.has(toggle) || toggle.getAttribute('aria-disabled') === 'true' || toggle.closest('fieldset:disabled')) return;
      expandedAddressControls.add(toggle);
      toggle.click();
    });
  };
  checkout.addEventListener('change', event => {
    if (event.target.closest('.wc-block-checkout__use-address-for-billing')) queueMicrotask(prepareFields);
  });
  prepareFields();
  new MutationObserver(prepareFields).observe(checkout, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled', 'aria-disabled'] });
})();
