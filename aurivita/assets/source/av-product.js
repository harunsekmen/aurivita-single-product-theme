import { AurivitaSection } from '@theme/av-components';
import { StandardEvents } from '@shopify/events';

class AurivitaProduct extends AurivitaSection {
  connectedCallback() {
    super.connectedCallback();
    this.initializeProduct();
  }

  updatedCallback() {
    super.updatedCallback();
    this.initializeProduct();
  }

  disconnectedCallback() {
    this.request?.abort();
    clearTimeout(this.cartResetTimer);
    this.cartOperation = null;
    super.disconnectedCallback();
  }

  initializeProduct() {
    this.request?.abort();
    clearTimeout(this.cartResetTimer);
    this.cartOperation = null;
    this.cartPending = false;
    this.selectionPending = false;
    this.cartState = 'idle';
    this.selectionFailed = false;
    const source = this.querySelector('[data-av-current-variant]');
    if (!source) return;
    this.currentVariant = JSON.parse(source.textContent);
    this.optionGroups = JSON.parse(this.parentElement.querySelector('[data-fw-product-options]')?.textContent || '[]');
    this.selection = [...(this.currentVariant?.options || [])];
    if (!this.currentVariant) this.selection = this.all('[data-fw-option]:checked').map(input => input.dataset.optionValue);
    const native = this.querySelector('[data-native-variant]');
    if (native) { native.disabled = true; native.hidden = true; }
    const identifier = this.querySelector('[ref="variantId"]');
    if (identifier) identifier.disabled = false;
    const picker = this.querySelector('[data-fw-options]');
    if (picker) picker.hidden = false;
    this.initializeCartFeedback();
    this.on(this, 'change', event => {
      if (event.target.matches('[data-fw-option]')) {
        this.selection = this.all('[data-fw-option]:checked').map(input => input.dataset.optionValue);
        this.resolveSelection();
      }
      if (event.target.matches('[data-fw-plan]')) { this.resetCartFeedback(); this.updateCommerce(); }
    });
    this.on(this, 'click', event => {
      const control = event.target.closest('[data-option-position][data-option-value]');
      if (!control) return;
      this.selection[Number(control.dataset.optionPosition)] = control.dataset.optionValue;
      this.resolveSelection();
    });
    const doseControls = this.all('[data-dose]');
    doseControls.forEach((control, index) => this.on(control, 'keydown', event => {
      if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
      event.preventDefault();
      const next = event.key === 'Home' ? 0 : event.key === 'End' ? doseControls.length - 1 : (index + (event.key === 'ArrowRight' ? 1 : -1) + doseControls.length) % doseControls.length;
      doseControls[next].click(); doseControls[next].focus();
    }));
    const home = this.querySelector('[data-fw-video-home]');
    const target = this.querySelector('[data-fw-video-target]');
    if (home && target) {
      const media = home.firstElementChild || target.firstElementChild;
      const breakpoint = matchMedia('(max-width: 749px)');
      const place = () => { if (media) (breakpoint.matches ? target : home).append(media); };
      this.on(breakpoint, 'change', place); place();
    }
    this.updateSelectionState();
    this.updateCommerce();
    const imageIndex = this.all('[data-image-id]').findIndex(image => String(image.dataset.imageId) === String(this.currentVariant?.image));
    if (imageIndex >= 0) this.selectImage?.(imageIndex);
  }

  updateSelectionState() {
    this.all('[data-option-position][data-option-value]').forEach(control => {
      const active = control.dataset.optionValue === this.selection[Number(control.dataset.optionPosition)];
      control.classList.toggle(control.hasAttribute('data-dose') ? 'is-active' : 'is-selected', active);
      control.setAttribute(control.hasAttribute('data-dose') ? 'aria-selected' : 'aria-pressed', String(active));
      if (control.hasAttribute('data-dose')) control.tabIndex = active ? 0 : -1;
    });
    this.all('[data-fw-option]').forEach(input => input.closest('label').classList.toggle('is-active', input.checked));
  }

  async resolveSelection() {
    if (this.cartPending) return;
    this.request?.abort();
    this.resetCartFeedback();
    this.selectionFailed = false;
    const request = new AbortController();
    this.request = request;
    const currentSelection = [...this.selection];
    const url = new URL(this.dataset.productUrl, location.origin);
    url.searchParams.set('section_id', this.dataset.sectionId);
    const view = new URL(location.href).searchParams.get('view');
    if (view) url.searchParams.set('view', view);
    const ids = currentSelection.map((name, index) => this.optionGroups[index]?.find(value => value.name === name)?.id);
    if (ids.length !== this.optionGroups.length || ids.some(id => !id)) {
      this.currentVariant = null;
      this.selectionFailed = true;
      const status = this.querySelector('[data-fw-selection-status]');
      if (status) status.textContent = this.dataset.error;
      this.setPending(false);
      this.updateCommerce();
      return;
    }
    url.searchParams.set('option_values', ids.join(','));
    const status = this.querySelector('[data-fw-selection-status]');
    this.updateSelectionState();
    this.setPending(true);
    if (status) status.textContent = '';
    try {
      const response = await fetch(url, { signal: request.signal });
      if (!response.ok) throw new Error(`Variant resolution: ${response.status}`);
      const document = new DOMParser().parseFromString(await response.text(), 'text/html');
      if (this.request !== request) return;
      const product = document.querySelector('av-product');
      const state = product?.querySelector('[data-av-current-variant]');
      if (!state) throw new Error('Missing product response');
      this.currentVariant = JSON.parse(state.textContent);
      const quantityComponent = this.querySelector('quantity-selector-component');
      const newQuantity = product.querySelector('quantity-selector-component input');
      if (quantityComponent && newQuantity) {
        quantityComponent.dataset.variantId = String(this.currentVariant?.id || '');
        quantityComponent.setCartQuantity?.(Number(newQuantity.dataset.cartQuantity) || 0);
        quantityComponent.updateConstraints?.(newQuantity.min, newQuantity.max || null, newQuantity.step);
      }
      const sourcePicker = product.querySelector('[data-fw-options]');
      const picker = this.querySelector('[data-fw-options]');
      if (picker?.classList.contains('fw-options') && sourcePicker) {
        const active = this.ownerDocument.activeElement?.getAttribute('name');
        const value = this.ownerDocument.activeElement?.value;
        picker.innerHTML = sourcePicker.innerHTML;
        if (active) this.all('[data-fw-option]').find(input => input.name === active && input.value === value)?.focus();
      }
      for (const card of this.all('[data-cc-offer-card]')) {
        const source = [...product.querySelectorAll('[data-cc-offer-card]')].find(item => item.dataset.optionValue === card.dataset.optionValue);
        for (const selector of ['[data-cc-price]', '[data-cc-compare]', '[data-cc-save]']) {
          const node = card.querySelector(selector), next = source?.querySelector(selector);
          if (!node || !next) continue;
          node.textContent = next.textContent;
          node.hidden = next.hidden;
        }
      }
      const plans = this.querySelector('[data-fw-selling-plans]');
      const newPlans = product.querySelector('[data-fw-selling-plans]');
      if (plans && newPlans) {
        const selectedPlan = plans.querySelector('select').value;
        plans.innerHTML = newPlans.innerHTML;
        const select = plans.querySelector('select');
        if ([...select.options].some(option => option.value === selectedPlan)) select.value = selectedPlan;
      } else if (plans) {
        plans.remove();
      } else if (newPlans) {
        const form = this.querySelector('form[action*="/cart/add"]');
        form?.append(newPlans);
      }
      this.querySelector('[data-av-current-variant]').textContent = state.textContent;
      const pageUrl = new URL(location.href);
      pageUrl.searchParams.delete('option_values');
      if (this.currentVariant) pageUrl.searchParams.set('variant', this.currentVariant.id);
      else { pageUrl.searchParams.delete('variant'); pageUrl.searchParams.set('option_values', ids.join(',')); }
      if (location.pathname === url.pathname) history.replaceState({}, '', pageUrl);
      const imageIndex = this.all('[data-image-id]').findIndex(image => String(image.dataset.imageId) === String(this.currentVariant?.image));
      if (imageIndex >= 0) this.selectImage?.(imageIndex);
      if (status) status.textContent = '';
      this.setPending(false);
      this.updateSelectionState();
      this.updateCommerce();
    } catch (error) {
      if (error.name === 'AbortError') return;
      if (this.request !== request) return;
      if (status) status.textContent = this.dataset.error;
      // The prior ID cannot be submitted for a failed new selection.
      this.currentVariant = null;
      this.selectionFailed = true;
      this.setPending(false);
      this.updateCommerce();
    }
  }

  setPending(pending) {
    this.selectionPending = pending;
    this.toggleAttribute('aria-busy', pending || this.cartPending);
    const button = this.querySelector('[ref="addToCartButton"]');
    if (button) button.disabled = pending || this.cartPending || !this.currentVariant?.available;
    const identifier = this.querySelector('[ref="variantId"]');
    if (identifier) identifier.disabled = pending;
    this.renderCartButton();
  }

  updateCommerce() {
    const variant = this.currentVariant;
    const identifier = this.querySelector('[ref="variantId"]');
    if (identifier) identifier.value = variant?.id || '';
    const quantity = this.querySelector('input[name="quantity"]');
    if (quantity) {
      if (quantity.type !== 'hidden') quantity.disabled = this.cartPending || this.selectionPending || !variant?.available;
      quantity.min = variant?.minimum || 1; quantity.step = variant?.increment || 1;
      if (variant?.maximum != null) quantity.max = variant.maximum; else quantity.removeAttribute('max');
      const minimum = Number(quantity.min), step = Number(quantity.step);
      let value = Math.max(minimum, Number(quantity.value) || minimum);
      value = minimum + Math.floor((value - minimum) / step) * step;
      const maximum = variant?.maximum;
      if (maximum != null) value = Math.min(value, minimum + Math.floor((maximum - minimum) / step) * step);
      quantity.value = value;
    }
    if (!this.cartPending) this.querySelector('quantity-selector-component')?.updateButtonStates?.();
    const form = this.querySelector('product-form-component');
    if (form) form.dataset.quantityDefault = variant?.minimum || 1;
    const plan = this.querySelector('[data-fw-plan]');
    const money = plan?.value ? plan.selectedOptions[0]?.dataset.money : variant?.money;
    const label = variant?.available ? this.dataset.add : variant ? this.dataset.sold : this.dataset.unavailable;
    const pending = this.selectionPending;
    const loadingLabel = this.cartFeedback?.dataset.adding || this.dataset.loading;
    const status = this.querySelector('[data-fw-selection-status]');
    if (status && !this.selectionFailed) status.textContent = pending || variant?.available ? '' : label;
    const button = this.querySelector('[ref="addToCartButton"]');
    const selectedPlanAvailable = !plan || (plan.selectedIndex >= 0 && [...plan.options].some(option => option.value === plan.value));
    if (button) button.disabled = pending || this.cartPending || this.cartState === 'uncertain' || !variant?.available || !selectedPlanAvailable;
    const cartLabel = this.querySelector('[data-cart-label]');
    if (cartLabel) cartLabel.textContent = this.cartState === 'idle' ? (pending ? loadingLabel : label) : this.cartButtonLabel;
    const price = this.querySelector('.local-price'); if (price) price.textContent = money || '';
    const compare = this.querySelector('.local-compare');
    if (compare) { compare.textContent = variant?.compareMoney || ''; compare.hidden = !variant || variant.compare <= variant.price || !!plan?.value; }
    const stock = this.querySelector('.cc-pdp-stock-line__status'); if (stock) stock.textContent = variant?.available ? this.dataset.stock : this.dataset.sold;
    window.dispatchEvent(new CustomEvent('av:variant', { detail: { variant: !pending && selectedPlanAvailable && variant ? { ...variant, money: money || variant.money } : null, label: pending ? loadingLabel : label } }));
  }

  initializeCartFeedback() {
    const form = this.querySelector('product-form-component');
    const feedback = this.querySelector('[data-fw-cart-feedback]');
    if (!form || !feedback) return;
    this.cartFeedback = feedback;
    this.dataset.fwUx = 'true';
    const nativeLive = form.querySelector('[ref="liveRegion"]');
    nativeLive?.setAttribute('aria-live', 'off');
    nativeLive?.removeAttribute('role');
    const nativeError = form.querySelector('[ref="addToCartTextError"]');
    nativeError?.removeAttribute('role');
    this.on(this, StandardEvents.cartLinesUpdate, event => {
      if (event.target !== form || event.action !== 'add' || !event.promise) return;
      const operation = this.cartOperation = {};
      const variant = this.currentVariant;
      const options = variant?.options?.filter(option => option !== 'Default Title').join(' · ') || '';
      const quantity = form.querySelector('input[name="quantity"]')?.value || '1';
      const money = this.querySelector('.local-price')?.textContent || '';
      const plan = this.querySelector('[data-fw-plan]');
      const planName = plan?.value ? plan.selectedOptions[0]?.textContent?.trim() : '';
      const summary = [options, planName, money].filter(Boolean).join(' · ');
      this.setCartState('pending', '');
      event.promise.then(result => {
        if (!this.isConnected || this.cartOperation !== operation) return;
        if (result.detail?.didError) {
          if (this.cartState === 'pending') this.setCartState('error', feedback.dataset.failed);
          return;
        }
        const message = feedback.dataset.success.replace('__quantity__', quantity).replace('__selection__', summary);
        this.setCartState('added', message);
        this.cartResetTimer = setTimeout(() => {
          if (this.isConnected && this.cartOperation === operation) {
            this.cartState = 'idle';
            this.renderCartButton();
          }
        }, 4000);
      }).catch(() => {
        if (this.isConnected && this.cartOperation === operation && this.cartState !== 'error') this.setCartState('uncertain', feedback.dataset.uncertain);
      });
    });
    this.on(this, StandardEvents.cartError, event => {
      if (event.target !== form || !this.cartOperation) return;
      const state = event.code === 'INVALID' ? 'error' : 'uncertain';
      this.setCartState(state, state === 'error' ? event.detail?.description || event.error || feedback.dataset.failed : feedback.dataset.uncertain);
    });
    this.on(this.querySelector('[data-fw-continue]'), 'click', () => {
      this.resetCartFeedback();
      this.querySelector('[ref="addToCartButton"]')?.focus({ preventScroll: true });
    });
    this.on(this.querySelector('input[name="quantity"]'), 'change', () => {
      if (!this.cartPending && this.cartState !== 'uncertain') this.resetCartFeedback();
    });
    this.renderCartButton();
  }

  resetCartFeedback() {
    if (this.cartPending) return;
    clearTimeout(this.cartResetTimer);
    this.cartOperation = null;
    this.setCartState('idle', '');
  }

  setCartState(state, message) {
    clearTimeout(this.cartResetTimer);
    this.cartState = state;
    this.cartPending = state === 'pending';
    this.toggleAttribute('aria-busy', this.selectionPending || this.cartPending);
    const feedback = this.cartFeedback;
    if (feedback) {
      feedback.dataset.state = state;
      feedback.querySelector('[data-fw-cart-status]').textContent = state === 'pending' ? '' : message;
      feedback.querySelector('[data-fw-cart-actions]').hidden = !['added', 'uncertain'].includes(state);
      feedback.querySelector('[data-fw-continue]').hidden = state === 'uncertain';
    }
    for (const control of this.all('[data-option-value], [data-fw-plan], input[name="quantity"], quantity-selector-component button')) {
      if (this.cartPending && !control.disabled) { control.disabled = true; control.dataset.fwCartLocked = 'true'; }
      else if (!this.cartPending && control.dataset.fwCartLocked) { control.disabled = false; delete control.dataset.fwCartLocked; }
    }
    if (!this.cartPending) this.querySelector('quantity-selector-component')?.updateButtonStates?.();
    this.renderCartButton();
  }

  renderCartButton() {
    const feedback = this.cartFeedback;
    const button = this.querySelector('[ref="addToCartButton"]');
    if (!button) return;
    const busy = this.selectionPending || this.cartPending;
    const state = busy ? 'pending' : this.cartState;
    const label = busy ? feedback?.dataset.adding || this.dataset.loading : this.cartState === 'added' ? feedback?.dataset.added : this.cartState === 'error' ? feedback?.dataset.retry : this.cartState === 'uncertain' ? feedback?.dataset.checkCart : this.currentVariant?.available ? this.dataset.add : this.currentVariant ? this.dataset.sold : this.dataset.unavailable;
    this.cartButtonLabel = label;
    button.dataset.fwCartState = state;
    button.setAttribute('aria-busy', String(busy));
    const cartLabel = this.querySelector('[data-cart-label]');
    if (cartLabel) cartLabel.textContent = label;
    this.updateCommerce();
    window.dispatchEvent(new CustomEvent('av:cart-state', { detail: { product: this, state, label, disabled: button.disabled } }));
  }
}

if (!customElements.get('av-product')) customElements.define('av-product', AurivitaProduct);
