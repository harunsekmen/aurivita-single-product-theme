import { AurivitaSection } from '@theme/av-components';

class AurivitaSticky extends AurivitaSection {
  connectedCallback() {
    super.connectedCallback();
    this.initializeSticky();
  }
  updatedCallback() {
    super.updatedCallback();
    this.initializeSticky();
  }
  initializeSticky() {
    this.observer?.disconnect();
    const product = document.querySelector('av-product');
    const original = product?.querySelector('[ref="addToCartButton"]');
    const sticky = this.querySelector('.cc-sticky-atc');
    if (!original || !sticky) return;
    const button = this.querySelector('[data-sticky-add]');
    const cartLabel = button.querySelector('.cc-sticky-atc__label');
    const idleLabel = cartLabel.textContent.trim();
    const update = ({ variant, label }) => {
      this.querySelector('.cc-sticky-atc__price-current').textContent = variant?.money ?? '';
      const compare = this.querySelector('.cc-sticky-atc__price-compare'); compare.textContent = variant?.compareMoney ?? ''; compare.hidden = !variant || variant.compare <= variant.price;
      button.disabled = original.disabled || !variant?.available;
      const cartState = original.dataset.fwCartState || 'idle';
      cartLabel.textContent = cartState !== 'idle' ? product.cartButtonLabel : original.disabled ? label : idleLabel;
      button.setAttribute('aria-label', [cartLabel.textContent, variant?.money, variant?.compare > variant?.price ? variant.compareMoney : ''].filter(Boolean).join(' '));
    };
    this.on(window, 'av:variant', event => update(event.detail));
    this.on(window, 'av:cart-state', event => {
      if (event.detail.product !== product) return;
      const { state, label, disabled } = event.detail;
      button.dataset.fwCartState = state;
      button.disabled = disabled;
      button.setAttribute('aria-busy', String(state === 'pending'));
      sticky.classList.toggle('is-loading', state === 'pending');
      cartLabel.textContent = state === 'idle' ? idleLabel : label;
      button.setAttribute('aria-label', [cartLabel.textContent, state === 'idle' ? product.currentVariant?.money : ''].filter(Boolean).join(' '));
    });
    update({ variant: product.currentVariant, label: product.dataset.add });
    this.on(this.querySelector('[data-sticky-add]'), 'click', () => original.click());
    this.observer = new IntersectionObserver(entries => { const visible = entries[0].isIntersecting; sticky.classList.toggle('is-visible', !visible); sticky.setAttribute('aria-hidden', String(visible)); this.all('button').forEach(button => { button.tabIndex = visible ? -1 : 0; }); }, { threshold: .1 });
    this.observer.observe(original);
  }
}


if (!customElements.get('av-sticky')) customElements.define('av-sticky', AurivitaSticky);
