(() => {
  'use strict';
  if (window.avNativeProductLoaded) return;
  window.avNativeProductLoaded = true;

  const products = new Set();
  const cart = { state: 'idle', active: null, message: '', timer: null, opened: false };
  const labels = { pending: 'Loading…', added: 'Added to cart', error: 'Try again', uncertain: 'Check cart' };
  const all = (root, selector) => Array.from(root.querySelectorAll(selector));
  const imageUrl = image => typeof image === 'string' ? image : image?.src || image?.url || '';
  const text = (root, selector, value) => all(root, selector).forEach(node => { node.textContent = value || ''; });
  let optionStatusId = 0;

  function setCartState(state, detail = {}) {
    if (!['idle', 'pending', 'added', 'error', 'uncertain'].includes(state)) return;
    clearTimeout(cart.timer);
    cart.state = state;
    if (state === 'pending' || state === 'idle') cart.message = '';
    if (state === 'added') cart.message = cart.active?.summary ? `${cart.active.summary} added to your cart.` : 'Item added to your cart.';
    if (state === 'error') cart.message = typeof detail.error === 'string' ? detail.error : detail.error?.message || 'The item could not be added to your cart. Please try again.';
    if (state === 'uncertain') cart.message = 'The add-to-cart result could not be verified. Check your cart before adding again.';
    for (const product of products) product.renderCommerce();
    if (state === 'added') {
      cart.timer = setTimeout(() => {
        if (!cart.active && cart.state === 'added') setCartState('idle');
      }, 4000);
    }
  }

  const seenCartEvents = new WeakSet();
  const receiveCartState = event => {
    if (seenCartEvents.has(event)) return;
    seenCartEvents.add(event);
    setCartState(event.detail?.state, event.detail || {});
  };
  document.addEventListener('av:cart-state', receiveCartState);
  window.addEventListener('av:cart-state', receiveCartState);

  function syncForm(form, product) {
    const variation = product.variant;
    const identifier = form.querySelector('[name="variation_id"]');
    if (identifier) identifier.value = variation?.id || '0';
    const attributes = form.querySelector('[data-av-attributes]');
    if (attributes) {
      const inputs = Object.entries(variation?.attributes || {}).map(([name, value]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        return input;
      });
      attributes.replaceChildren(...inputs);
    }
    const quantity = form.querySelector('[name="quantity"]');
    if (quantity && form.matches('[data-av-sticky-form]')) quantity.value = product.quantity();
  }

  class Product {
    constructor(root) {
      this.root = root;
      this.abort = new AbortController();
      try { this.data = JSON.parse(root.querySelector('[data-av-product-data]').textContent); }
      catch { return; }
      this.reference = root.dataset.reference === 'true';
      this.variations = this.data.variations || [];
      this.variant = this.data.selected || null;
      this.servings = Number(this.variant?.servings || 30);
      this.packs = Number(this.variant?.packs || 3);
      this.attributes = { ...(this.variant?.attributes || {}) };
      this.form = root.querySelector('[data-av-product-form]');
      this.gallery = 0;
      this.ready = true;
      products.add(this);
      this.bind();
      this.initializeMedia();
      this.initializeFaqs();
      this.renderSelection();
      this.renderCommerce();
      this.selectVariantImage();
      this.initializeSticky();
    }

    on(target, event, handler, options = {}) {
      target?.addEventListener(event, handler, { ...options, signal: this.abort.signal });
    }

    stickyRoots() {
      return all(document, 'av-sticky[data-product-id]').filter(root => root.dataset.productId === String(this.data.id));
    }

    quantity() {
      const input = this.form?.querySelector('[name="quantity"]');
      return Math.max(1, Number(input?.value) || 1);
    }

    bind() {
      this.on(this.root, 'click', event => {
        const option = event.target.closest('[data-servings], [data-packs]');
        if (option && this.root.contains(option)) {
          if (cart.active || cart.state === 'pending' || option.disabled) return;
          let selectionMessage = '';
          if (option.hasAttribute('data-servings')) {
            const servings = Number(option.dataset.servings);
            if (servings !== this.servings) {
              const available = this.variations.filter(v => Number(v.servings) === servings && v.available);
              if (available.length && !available.some(v => Number(v.packs) === this.packs)) {
                this.packs = Math.min(...available.map(v => Number(v.packs)));
                selectionMessage = `${this.packs} packs selected with ${servings} servings.`;
              }
            }
            this.servings = servings;
          }
          if (option.hasAttribute('data-packs')) this.packs = Number(option.dataset.packs);
          this.resolve(selectionMessage);
        }
        if (event.target.closest('[data-av-retry]')) this.submit();
        if (event.target.closest('[data-fw-continue]') && !cart.active && cart.state === 'added') {
          setCartState('idle');
          this.root.querySelector('[data-av-add]')?.focus({ preventScroll: true });
        }
        const viewCart = event.target.closest('[data-av-view-cart]');
        if (viewCart && window.AurivitaCart?.open) {
          event.preventDefault();
          window.AurivitaCart.open();
        }
      });
      this.on(this.root, 'change', event => {
        const option = event.target.closest('[data-fw-option]');
        if (option && !cart.active && cart.state !== 'pending') {
          this.attributes[option.dataset.attribute] = option.value;
          this.resolve();
        }
        if (event.target.matches('[name="quantity"]')) {
          if (cart.state === 'added' || cart.state === 'error') setCartState('idle');
          this.renderCommerce();
        }
      });
      this.on(this.form, 'submit', event => {
        if (!window.AurivitaCart?.add) return;
        const customFields = Array.from(new FormData(this.form).keys()).some(key => !['add-to-cart','product_id','variation_id','quantity'].includes(key) && !key.startsWith('attribute_'));
        if (customFields) return;
        event.preventDefault();
        this.submit();
      });
      const doses = all(this.root, '[data-servings]');
      doses.forEach((dose, index) => this.on(dose, 'keydown', event => {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
        event.preventDefault();
        const enabled = doses.filter(item => !item.disabled);
        const current = enabled.indexOf(dose);
        const next = event.key === 'Home' ? 0 : event.key === 'End' ? enabled.length - 1 : (current + (event.key === 'ArrowRight' ? 1 : -1) + enabled.length) % enabled.length;
        enabled[next]?.click();
        enabled[next]?.focus();
      }));
    }

    resolve(selectionMessage = '') {
      this.selectionMessage = selectionMessage;
      if (cart.state === 'added' || cart.state === 'error') setCartState('idle');
      if (this.reference) {
        this.variant = this.variations.find(v => Number(v.servings) === this.servings && Number(v.packs) === this.packs) || null;
      } else {
        const keys = Array.from(new Set(all(this.root, '[data-fw-option]').map(option => option.dataset.attribute)));
        const complete = keys.every(key => this.attributes[key]);
        const match = complete ? this.variations.find(v => keys.every(key => !v.attributes[key] || v.attributes[key] === this.attributes[key])) : null;
        this.variant = match ? { ...match, attributes: { ...match.attributes, ...this.attributes } } : null;
      }
      this.renderSelection();
      this.renderCommerce();
      this.selectVariantImage();
      this.updateUrl();
      this.root.dispatchEvent(new CustomEvent('av:variant', { bubbles: true, detail: { product: this.root, variant: this.variant } }));
    }

    updateUrl() {
      const url = new URL(location.href);
      if (url.pathname !== new URL(this.data.url, location.origin).pathname) return;
      url.searchParams.delete('variant');
      for (const key of Array.from(url.searchParams.keys())) if (key.startsWith('attribute_')) url.searchParams.delete(key);
      const attributes = this.variant?.attributes || this.attributes;
      if (this.variant?.source_id) url.searchParams.set('variant', this.variant.source_id);
      for (const [key, value] of Object.entries(attributes || {})) url.searchParams.set(key, value);
      history.replaceState(history.state, '', url);
    }

    renderSelection() {
      const pending = !!cart.active || cart.state === 'pending';
      const doses = all(this.root, '[data-servings]');
      for (const dose of doses) {
        const active = Number(dose.dataset.servings) === this.servings;
        dose.classList.toggle('is-active', active);
        dose.setAttribute('aria-selected', String(active));
        dose.disabled = pending || !this.variations.some(v => Number(v.servings) === Number(dose.dataset.servings) && v.available);
      }
      const tabStop = doses.find(dose => !dose.disabled && Number(dose.dataset.servings) === this.servings) || doses.find(dose => !dose.disabled);
      for (const dose of doses) dose.tabIndex = dose === tabStop ? 0 : -1;
      for (const card of all(this.root, '[data-packs]')) {
        const packs = Number(card.dataset.packs);
        const variant = this.variations.find(v => Number(v.servings) === this.servings && Number(v.packs) === packs);
        const active = packs === this.packs;
        card.classList.toggle('is-selected', active);
        card.setAttribute('aria-pressed', String(active));
        card.disabled = pending || !variant?.available;
        text(card, '[data-cc-price]', variant?.money || 'Unavailable');
        const compare = card.querySelector('[data-cc-compare]');
        const save = card.querySelector('[data-cc-save]');
        const discounted = variant && Number(variant.regular_price) > Number(variant.price);
        if (compare) { compare.textContent = variant?.compare_money || ''; compare.hidden = !discounted; }
        if (save) {
          save.hidden = !discounted;
          if (discounted) save.textContent = `Save ${Math.round((1 - Number(variant.price) / Number(variant.regular_price)) * 100)}%`;
        }
      }
      for (const option of all(this.root, '[data-fw-option]')) {
        option.checked = this.attributes[option.dataset.attribute] === option.value;
        const label = option.closest('label');
        label?.classList.toggle('is-active', option.checked);
        const available = this.variations.filter(v => v.available && (!v.attributes?.[option.dataset.attribute] || v.attributes[option.dataset.attribute] === option.value));
        const compatible = available.some(v => Object.entries(v.attributes || {}).every(([key, value]) => key === option.dataset.attribute || !value || !this.attributes[key] || this.attributes[key] === value));
        option.disabled = pending;
        label?.classList.toggle('is-unavailable', !compatible);
        if (!label) continue;
        let status = label.querySelector('[data-fw-option-status]');
        if (!status && !compatible) {
          status = document.createElement('span');
          status.className = 'fw-option-status';
          status.id = `av-option-status-${++optionStatusId}`;
          status.setAttribute('data-fw-option-status', '');
          label.append(status);
        }
        if (status) {
          status.hidden = compatible;
          status.textContent = compatible ? '' : available.length ? 'Change the other options' : 'Out of stock';
          const descriptions = new Set((option.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));
          if (compatible) descriptions.delete(status.id);
          else descriptions.add(status.id);
          if (descriptions.size) option.setAttribute('aria-describedby', Array.from(descriptions).join(' '));
          else option.removeAttribute('aria-describedby');
        }
      }
    }

    renderCommerce() {
      const variant = this.data.variable ? this.variant : this.data;
      const available = !!variant?.available;
      const locked = !!cart.active || ['pending', 'added', 'uncertain'].includes(cart.state);
      const label = labels[cart.state] || (available ? this.root.dataset.add : variant ? this.root.dataset.sold : this.root.dataset.unavailable);
      this.root.setAttribute('aria-busy', String(!!cart.active || cart.state === 'pending'));
      const roots = [this.root, ...this.stickyRoots()];
      for (const root of roots) {
        for (const form of all(root, '[data-av-product-form], [data-av-sticky-form]')) syncForm(form, this);
        for (const button of all(root, '[data-av-add]')) {
          button.disabled = locked || !available;
          button.dataset.fwCartState = cart.state;
          button.setAttribute('aria-busy', String(cart.state === 'pending'));
          const buttonLabel = root.matches('av-sticky') && cart.state === 'idle' && available ? root.dataset.addLabel : label;
          text(button, '[data-cart-label], .cc-sticky-atc__label', buttonLabel);
          button.setAttribute('aria-label', [buttonLabel, cart.state === 'idle' ? variant?.money : ''].filter(Boolean).join(' '));
          text(button, '.local-price, .cc-sticky-atc__price-current', variant?.money);
          for (const compare of all(button, '.local-compare, .cc-sticky-atc__price-compare')) {
            compare.textContent = variant?.compare_money || '';
            compare.hidden = !variant || Number(variant.regular_price) <= Number(variant.price);
          }
        }
        root.querySelector('.cc-sticky-atc')?.classList.toggle('is-loading', cart.state === 'pending');
        const stock = root.querySelector('.cc-pdp-stock-line__status, .cc-sticky-atc__stock-status');
        if (stock) {
          stock.dataset.availableLabel ||= stock.textContent;
          stock.textContent = available ? stock.dataset.availableLabel === 'Sold out' ? this.root.dataset.stock : stock.dataset.availableLabel : this.root.dataset.sold;
        }
      }
      const feedback = this.root.querySelector('[data-fw-cart-feedback]');
      if (feedback) {
        feedback.dataset.state = cart.state;
        text(feedback, '[data-fw-cart-status]', cart.message);
        feedback.querySelector('[data-fw-cart-actions]').hidden = !['added', 'error', 'uncertain'].includes(cart.state);
        feedback.querySelector('[data-fw-continue]').hidden = cart.state !== 'added';
        feedback.querySelector('[data-av-retry]').hidden = cart.state !== 'error';
      }
      const selectionStatus = available ? this.selectionMessage || '' : variant ? this.root.dataset.sold : this.root.dataset.unavailable;
      for (const status of all(this.root, '[data-fw-selection-status]')) {
        if (status.textContent !== selectionStatus) status.textContent = selectionStatus;
      }
      const quantity = this.form?.querySelector('input[type="number"][name="quantity"]');
      if (quantity && !quantity.hasAttribute('data-av-permanent-disabled')) {
        if (quantity.disabled && !quantity.dataset.avCartLocked) quantity.dataset.avPermanentDisabled = 'true';
        else { quantity.disabled = !!cart.active || cart.state === 'pending'; quantity.dataset.avCartLocked = 'true'; }
      }
      this.renderSelection();
    }

    async submit() {
      if (!window.AurivitaCart?.add) { this.form?.requestSubmit(); return; }
      if (cart.active || ['pending', 'added', 'uncertain'].includes(cart.state)) return;
      const variant = this.data.variable ? this.variant : this.data;
      if (!variant?.available || !this.form?.checkValidity()) return;
      const quantity = this.quantity();
      const selection = this.reference ? `${this.servings} servings · ${this.packs} packs` : variant.label;
      const operation = { product: this, summary: [this.data.title, selection, `${quantity} items`, variant.money].filter(Boolean).join(' · ') };
      cart.active = operation;
      cart.opened = false;
      setCartState('pending');
      try {
        const confirmedCart = await window.AurivitaCart.add(Number(variant.id), quantity, variant.attributes || {});
        if (cart.active !== operation) return;
        if (!confirmedCart || !Array.isArray(confirmedCart.items)) throw Object.assign(new Error('The cart response could not be verified.'), { uncertain: true });
        cart.active = null;
        setCartState('added', { cart: confirmedCart });
        cart.message = `${operation.summary} added to your cart.`;
        for (const product of products) product.renderCommerce();
        if (!cart.opened && window.AurivitaCart.open) { cart.opened = true; window.AurivitaCart.open(); }
      } catch (error) {
        if (cart.active !== operation) return;
        cart.active = null;
        const state = cart.state === 'error' || error?.uncertain === false ? 'error' : 'uncertain';
        setCartState(state, { error });
      }
    }

    initializeSticky() {
      for (const sticky of this.stickyRoots()) {
        this.on(sticky.querySelector('[data-av-sticky-form]'), 'submit', event => {
          if (!window.AurivitaCart?.add) return;
          event.preventDefault();
          this.submit();
        });
      }
      const original = this.root.querySelector('[data-av-add]');
      if (!original || !this.stickyRoots().length) return;
      const update = visible => {
        for (const sticky of this.stickyRoots()) {
          const bar = sticky.querySelector('.cc-sticky-atc');
          bar?.classList.toggle('is-visible', visible);
          bar?.setAttribute('aria-hidden', String(!visible));
          bar?.toggleAttribute('inert', !visible);
        }
      };
      if ('IntersectionObserver' in window) {
        this.stickyObserver = new IntersectionObserver(entries => update(!entries[0].isIntersecting), { threshold: 0.1 });
        this.stickyObserver.observe(original);
      } else {
        const check = () => { const box = original.getBoundingClientRect(); update(box.bottom < 0 || box.top >= innerHeight); };
        this.on(window, 'scroll', check, { passive: true });
        this.on(window, 'resize', check);
        check();
      }
    }

    selectImage(index) {
      const slides = all(this.root, '.cc-pdp__main-slide');
      const thumbs = all(this.root, '.cc-pdp__thumb');
      if (!slides.length) return;
      this.gallery = Math.max(0, Math.min(index, slides.length - 1));
      slides.forEach((slide, current) => { slide.hidden = current !== this.gallery; slide.classList.toggle('swiper-slide-active', current === this.gallery); });
      thumbs.forEach((thumb, current) => { thumb.classList.toggle('swiper-slide-thumb-active', current === this.gallery); thumb.setAttribute('aria-pressed', String(current === this.gallery)); });
      const previous = this.root.querySelector('.cc-pdp__main-arrow--prev');
      const next = this.root.querySelector('.cc-pdp__main-arrow--next');
      for (const [button, disabled] of [[previous, this.gallery === 0], [next, this.gallery === slides.length - 1]]) {
        if (!button) continue;
        button.disabled = disabled;
        button.setAttribute('aria-disabled', String(disabled));
        button.classList.toggle('swiper-button-disabled', disabled);
      }
      const peek = this.root.querySelector('.gallery-peek');
      if (peek) {
        peek.hidden = this.gallery === slides.length - 1;
        const image = slides[Math.min(this.gallery + 1, slides.length - 1)].querySelector('img');
        const preview = peek.querySelector('img');
        if (image && preview) { preview.srcset = image.srcset; preview.src = image.src; }
      }
      const wrap = this.root.querySelector('.cc-pdp__thumbs-wrap, .fw-product__thumbs');
      const thumb = thumbs[this.gallery];
      if (wrap && thumb) {
        if (matchMedia('(min-width:750px)').matches && !wrap.matches('.fw-product__thumbs')) wrap.scrollTop = Math.max(0, thumb.offsetTop - wrap.clientHeight / 2 + thumb.clientHeight / 2);
        else wrap.scrollLeft = Math.max(0, thumb.offsetLeft - wrap.clientWidth / 2 + thumb.clientWidth / 2);
      }
    }

    selectVariantImage() {
      const url = imageUrl(this.variant?.image);
      if (!url) return;
      const slides = all(this.root, '.cc-pdp__main-slide');
      const index = slides.findIndex(slide => slide.querySelector('img')?.src === new URL(url, location.origin).href || slide.dataset.imageId === String(this.variant?.image?.id));
      if (index >= 0) this.selectImage(index);
      else {
        const slide = this.variantSlide || document.createElement('div');
        slide.className = 'swiper-slide cc-pdp__main-slide';
        slide.dataset.imageId = `variation-${this.variant.id}`;
        slide.setAttribute('role', 'group');
        slide.setAttribute('aria-label', this.variant.label || this.data.title);
        const image = slide.querySelector('img') || document.createElement('img');
        image.className = 'cc-pdp__main-image';
        image.src = url;
        image.alt = this.variant.image_alt || this.data.title;
        if (!image.parentElement) slide.append(image);
        const track = slides[0]?.parentElement;
        if (track) {
          if (!this.variantSlide) {
            track.insertBefore(slide, slides[slides.length - 1].nextSibling);
            this.variantSlide = slide;
            const thumbs = all(this.root, '.cc-pdp__thumb');
            const thumb = document.createElement('button');
            thumb.type = 'button';
            thumb.className = 'swiper-slide cc-pdp__thumb';
            thumb.setAttribute('aria-label', 'Selected variation image');
            const preview = image.cloneNode();
            preview.className = 'cc-pdp__thumb-image';
            thumb.append(preview);
            thumbs[0]?.parentElement.append(thumb);
            this.variantThumb = thumb;
            this.on(thumb, 'click', () => this.selectImage(all(this.root, '.cc-pdp__main-slide').indexOf(slide)));
          }
          const preview = this.variantThumb?.querySelector('img');
          if (preview) { preview.src = url; preview.alt = image.alt; }
          this.selectImage(all(this.root, '.cc-pdp__main-slide').indexOf(slide));
        }
      }
      for (const sticky of this.stickyRoots()) {
        const image = sticky.querySelector('.cc-sticky-atc__image img');
        if (image) { image.srcset = ''; image.src = url; image.alt = this.variant.image_alt || this.data.title; }
      }
    }

    initializeMedia() {
      const slides = all(this.root, '.cc-pdp__main-slide');
      const thumbs = all(this.root, '.cc-pdp__thumb');
      thumbs.forEach((thumb, index) => this.on(thumb, 'click', () => this.selectImage(index)));
      this.on(this.root.querySelector('.cc-pdp__main-arrow--prev'), 'click', () => this.selectImage(this.gallery - 1));
      this.on(this.root.querySelector('.cc-pdp__main-arrow--next'), 'click', () => this.selectImage(this.gallery + 1));
      const gallery = this.root.querySelector('.cc-pdp__main-wrap');
      if (gallery) gallery.tabIndex = 0;
      this.on(gallery, 'keydown', event => {
        if (!['ArrowLeft', 'ArrowRight'].includes(event.key) || event.target.matches('button')) return;
        event.preventDefault();
        this.selectImage(this.gallery + (event.key === 'ArrowRight' ? 1 : -1));
      });
      let start = null;
      this.on(gallery, 'touchstart', event => {
        const point = event.changedTouches[0];
        start = point ? { x: point.clientX, y: point.clientY } : null;
      }, { passive: true });
      this.on(gallery, 'touchend', event => {
        const point = event.changedTouches[0];
        if (!start || !point) { start = null; return; }
        const x = point.clientX - start.x;
        const y = point.clientY - start.y;
        start = null;
        if (Math.abs(x) > 50 && Math.abs(x) > Math.abs(y)) this.selectImage(this.gallery + (x < 0 ? 1 : -1));
      }, { passive: true });
      this.on(gallery, 'touchcancel', () => { start = null; }, { passive: true });
      this.selectImage(Math.max(0, slides.findIndex(slide => !slide.hidden)));
      const home = this.root.querySelector('[data-fw-video-home]');
      const target = this.root.querySelector('[data-fw-video-target]');
      const videos = home?.firstElementChild || target?.firstElementChild;
      if (home && target && videos) {
        const mobile = matchMedia('(max-width:749px)');
        const place = () => (mobile.matches ? target : home).append(videos);
        this.on(mobile, 'change', place);
        place();
      }
    }

    initializeFaqs() {
      all(this.root, '.cc-pdp-faqs__toggle').forEach((toggle, index) => {
        const panel = toggle.nextElementSibling;
        if (!panel) return;
        panel.id = `${this.root.dataset.sectionId}-faq-${index}`;
        toggle.setAttribute('aria-controls', panel.id);
        const accordion = window.AurivitaAccordion(toggle, panel, toggle.closest('.cc-pdp-faqs__item'));
        this.abort.signal.addEventListener('abort', () => accordion.destroy(), { once: true });
        this.on(toggle, 'click', () => accordion.set(toggle.getAttribute('aria-expanded') !== 'true'));
      });
    }

    destroy() {
      this.abort.abort();
      this.stickyObserver?.disconnect();
      all(this.root, 'video').forEach(video => video.pause());
      products.delete(this);
    }
  }

  function start() {
    if (!customElements.get('av-product')) {
      customElements.define('av-product', class extends HTMLElement {
        connectedCallback() { this.avController = new Product(this); }
        disconnectedCallback() { this.avController?.destroy(); }
      });
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
  else start();
})();
