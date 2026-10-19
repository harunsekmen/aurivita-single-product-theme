(() => {
  const root = document.querySelector('.wp-block-woocommerce-checkout');
  const config = window.AurivitaTurkey;
  if (!root || !config || !window.wp?.data) return;
  const cartStore = 'wc/store/cart';
  const validationStore = 'wc/store/validation';
  const touched = new Set();
  const previousStates = new Map();
  const setNativeValue = (input, value) => {
    Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set.call(input, value);
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
  };
  const sync = () => {
    for (const group of ['shipping', 'billing']) {
      const country = root.querySelector(`#${group}-country`);
      const countryWrap = country?.closest('.wc-block-components-address-form__country');
      if (country && country.value !== 'TR' && !country.disabled) {
        const action = group === 'shipping' ? 'setShippingAddress' : 'setBillingAddress';
        wp.data.dispatch(cartStore)[action]({ country: 'TR', state: '', city: '' });
      }
      countryWrap?.classList.toggle('av-country-fixed', country.value === 'TR');
      const input = root.querySelector(`#${group}-city`);
      const state = root.querySelector(`#${group}-state`);
      if (!input || !state || !Object.keys(config.districts).length) continue;
      if (state.options[0]?.value === '' && state.options[0].textContent !== config.provincePlaceholder) state.options[0].textContent = config.provincePlaceholder;
      const wrap = input.closest('.wc-block-components-text-input');
      const list = config.districts[state.value] || [];
      let select = wrap.querySelector('.av-district-select');
      if (!select) {
        select = document.createElement('select');
        select.className = 'av-district-select';
        select.id = `${group}-district`;
        select.name = `${group}-district`;
        select.autocomplete = `section-${group} ${group} address-level2`;
        select.setAttribute('aria-label', config.districtLabel);
        wrap.append(select);
        select.addEventListener('change', () => {
          const liveInput = root.querySelector(`#${group}-city`);
          if (liveInput) setNativeValue(liveInput, select.value);
          touched.add(group);
          schedule();
        });
        select.addEventListener('blur', () => {
          touched.add(group);
          const liveInput = root.querySelector(`#${group}-city`);
          liveInput?.dispatchEvent(new FocusEvent('focusout', { bubbles: true }));
          schedule();
        });
      }
      const current = input.value;
      const signature = JSON.stringify([state.value, current]);
      if (select.dataset.signature !== signature) {
        const placeholder = new Option(list.length ? config.chooseDistrict : config.chooseProvince, '');
        placeholder.disabled = true;
        const options = [placeholder, ...list.map(district => new Option(district, district))];
        // Saved legacy text remains visible until the customer chooses a valid district.
        if (list.length && current && !list.includes(current)) options.splice(1, 0, new Option(current, current));
        select.replaceChildren(...options);
        select.value = list.length ? current : '';
        select.dataset.signature = signature;
      }
      const disabled = !list.length || input.disabled || state.disabled || !!input.closest('fieldset:disabled');
      if (select.disabled !== disabled) select.disabled = disabled;
      const required = input.required || input.getAttribute('aria-required') === 'true';
      if (select.required !== required) select.required = required;
      input.classList.add('av-native-district');
      input.setAttribute('aria-hidden', 'true');
      input.tabIndex = -1;
      wrap.classList.add('av-has-district');
      const errorId = `av-${group}-district`;
      const error = wp.data.select(validationStore).getValidationError(errorId);
      const invalid = list.length && !list.includes(current);
      if (invalid) {
        const hidden = error ? error.hidden && !touched.has(group) : !touched.has(group);
        if (!error || error.hidden !== hidden) wp.data.dispatch(validationStore).setValidationErrors({ [errorId]: { message: config.invalidDistrict, hidden } });
      } else if (error) wp.data.dispatch(validationStore).clearValidationError(errorId);
      const visibleError = wp.data.select(validationStore).getValidationError(errorId);
      let feedback = wrap.querySelector('[data-av-district-error]');
      if (visibleError && !visibleError.hidden) {
        if (!feedback) {
          feedback = document.createElement('div');
          feedback.className = 'wc-block-components-validation-error';
          feedback.dataset.avDistrictError = '';
          feedback.id = `validate-error-${errorId}`;
          feedback.setAttribute('role', 'alert');
          feedback.textContent = config.invalidDistrict;
          wrap.append(feedback);
        }
      } else feedback?.remove();
      const attributes = {
        'aria-required': input.getAttribute('aria-required'),
        'aria-invalid': visibleError && !visibleError.hidden ? 'true' : input.getAttribute('aria-invalid'),
        'aria-describedby': visibleError && !visibleError.hidden
          ? [input.getAttribute('aria-describedby'), feedback.id].filter(Boolean).join(' ')
          : input.getAttribute('aria-describedby'),
        'aria-errormessage': input.getAttribute('aria-errormessage'),
      };
      for (const [attribute, value] of Object.entries(attributes)) {
        if (value === null) {
          if (select.hasAttribute(attribute)) select.removeAttribute(attribute);
        } else if (select.getAttribute(attribute) !== value) select.setAttribute(attribute, value);
      }
      previousStates.set(group, state.value);
    }
  };
  let scheduled = false;
  function schedule() {
    if (scheduled) return;
    scheduled = true;
    requestAnimationFrame(() => { scheduled = false; sync(); });
  }
  root.addEventListener('change', event => {
    const match = event.target.id.match(/^(shipping|billing)-state$/);
    if (match && previousStates.has(match[1]) && previousStates.get(match[1]) !== event.target.value) {
      const input = root.querySelector(`#${match[1]}-city`);
      const state = event.target.value;
      if (input && !(config.districts[state] || []).includes(input.value)) {
        const action = match[1] === 'shipping' ? 'setShippingAddress' : 'setBillingAddress';
        // Update both together: the old city input callback can restore the previous province.
        queueMicrotask(() => wp.data.dispatch(cartStore)[action]({ state, city: '' }));
      }
      touched.delete(match[1]);
    }
    schedule();
  });
  root.addEventListener('focusin', event => {
    if (event.target.matches('.av-native-district')) event.target.parentElement.querySelector('.av-district-select')?.focus();
  });
  new MutationObserver(schedule).observe(root, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled', 'aria-invalid', 'aria-describedby', 'aria-errormessage'] });
  wp.data.subscribe(schedule, cartStore);
  wp.data.subscribe(schedule, validationStore);
  sync();
})();
