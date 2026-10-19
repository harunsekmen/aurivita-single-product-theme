(() => {
  'use strict';

  if (window.AurivitaContent) return;
  const selector = '[data-av-content]';
  const instances = new WeakMap();
  const videoInstances = new WeakSet();
  const focusOrigins = new WeakMap();
  const reducedMotion = () => matchMedia('(prefers-reduced-motion: reduce)').matches;
  const scrollBehavior = () => reducedMotion() ? 'auto' : 'smooth';
  const all = (root, query) => [...root.querySelectorAll(query)];
  const controlledPanel = (root, button) => {
    const id = button.getAttribute('aria-controls');
    return id ? root.querySelector('#' + CSS.escape(id)) : button.nextElementSibling;
  };

  class ContentSection {
    constructor(root) {
      this.root = root;
      this.controller = new AbortController();
      this.cleanups = [];
      this.accordions();
      this.tabs();
      this.reviews();
      this.stats();
      this.compare();
      this.dialogs();
      this.cleanups.push(initVideos(root));
    }

    on(target, type, handler, options = {}) {
      target?.addEventListener(type, handler, { ...options, signal: this.controller.signal });
    }

    destroy() {
      this.controller.abort();
      this.cleanups.forEach(cleanup => cleanup());
    }

    resize(target, callback) {
      if (typeof ResizeObserver === 'undefined') {
        this.on(window, 'resize', callback);
        return;
      }
      const observer = new ResizeObserver(callback);
      observer.observe(target);
      this.cleanups.push(() => observer.disconnect());
    }

    swipe(target, move) {
      let start;
      this.on(target, 'touchstart', event => {
        const point = event.changedTouches[0];
        start = { x: point.clientX, y: point.clientY };
      }, { passive: true });
      this.on(target, 'touchend', event => {
        if (!start) return;
        const point = event.changedTouches[0];
        const x = point.clientX - start.x;
        const y = point.clientY - start.y;
        start = null;
        if (Math.abs(x) > 50 && Math.abs(x) > Math.abs(y)) move(x < 0 ? 1 : -1);
      }, { passive: true });
      this.on(target, 'touchcancel', () => { start = null; }, { passive: true });
    }

    accordions() {
      const query = '.cc-faq__toggle,.cc-pdp-faqs__toggle,[data-csi-accordion]';
      all(this.root, query).forEach(button => {
        const item = button.closest('.cc-faq__item,.cc-pdp-faqs__item,.csi-item');
        const panel = controlledPanel(this.root, button);
        if (!panel) return;
        const accordion = window.AurivitaAccordion(button, panel, item);
        this.cleanups.push(() => accordion.destroy());
        this.on(button, 'click', () => accordion.set(button.getAttribute('aria-expanded') !== 'true'));
      });
    }

    tabs() {
      for (const region of all(this.root, '.cc-doctors-see,.cc-timeline')) {
        const tabs = all(region, '[role="tab"]');
        const slides = all(region, '.swiper-slide');
        if (!tabs.length) continue;
        let current = Math.max(0, tabs.findIndex(tab => tab.getAttribute('aria-selected') === 'true'));
        const activate = index => {
          current = (index + tabs.length) % tabs.length;
          tabs.forEach((tab, position) => {
            const active = position === current;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-selected', String(active));
            tab.tabIndex = active ? 0 : -1;
            const panel = controlledPanel(this.root, tab);
            if (panel) {
              panel.hidden = !active;
              panel.classList.toggle('is-active', active);
            }
          });
          slides.forEach((slide, position) => {
            slide.hidden = position !== current;
            slide.classList.toggle('swiper-slide-active', position === current);
            slide.classList.toggle('swiper-slide-next', position === current + 1);
          });
        };
        tabs.forEach((tab, index) => {
          this.on(tab, 'click', () => activate(index));
          this.on(tab, 'keydown', event => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            const next = event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1
              : index + (event.key === 'ArrowRight' ? 1 : -1);
            activate(next);
            tabs[current].focus();
          });
        });
        this.swipe(region.querySelector('.swiper'), direction => activate(current + direction));
        activate(current);
      }
    }

    reviews() {
      const track = this.root.querySelector('.cc-customer-notice__swiper');
      const pagination = this.root.querySelector('.cc-customer-notice__pagination');
      const slides = track ? all(track, '.swiper-slide') : [];
      if (!track || !pagination || !slides.length) return;
      const previous = this.root.querySelector('.cc-customer-notice__arrow--prev');
      const next = this.root.querySelector('.cc-customer-notice__arrow--next');
      const prototype = pagination.firstElementChild?.cloneNode(true);
      if (!prototype) return;
      const label = prototype.getAttribute('aria-label') || '';
      let offsets = [0];
      let current = 0;
      const update = () => {
        current = offsets.reduce((closest, offset, index) =>
          Math.abs(offset - track.scrollLeft) < Math.abs(offsets[closest] - track.scrollLeft) ? index : closest, 0);
        all(pagination, '.swiper-pagination-bullet').forEach((bullet, index) => {
          const active = index === current;
          bullet.classList.toggle('swiper-pagination-bullet-active', active);
          bullet.setAttribute('aria-pressed', String(active));
          if (active) bullet.setAttribute('aria-current', 'true');
          else bullet.removeAttribute('aria-current');
        });
        [[previous, track.scrollLeft <= 1], [next, track.scrollLeft >= offsets.at(-1) - 1]].forEach(([button, disabled]) => {
          if (!button) return;
          button.disabled = disabled;
          button.setAttribute('aria-disabled', String(disabled));
          button.classList.toggle('swiper-button-disabled', disabled);
        });
        const first = slides[0].offsetLeft;
        slides.forEach(slide => {
          const active = Math.abs(slide.offsetLeft - first - track.scrollLeft) < 2;
          slide.classList.toggle('swiper-slide-active', active);
        });
      };
      const show = index => track.scrollTo({ left: offsets[Math.max(0, Math.min(index, offsets.length - 1))], behavior: scrollBehavior() });
      const layout = () => {
        if (!track.clientWidth) return;
        const wrapper = track.querySelector('.swiper-wrapper');
        const gap = parseFloat(getComputedStyle(wrapper).gap) || 0;
        const step = slides[1] ? slides[1].offsetLeft - slides[0].offsetLeft : slides[0].offsetWidth + gap;
        const visible = Math.max(1, Math.round((track.clientWidth + gap) / Math.max(1, step)));
        const maximum = Math.max(0, track.scrollWidth - track.clientWidth);
        offsets = Array.from({ length: Math.ceil(slides.length / visible) }, (_, page) => Math.min(page * visible * step, maximum));
        const count = offsets.length;
        while (pagination.children.length > count) pagination.lastElementChild.remove();
        while (pagination.children.length < count) pagination.append(prototype.cloneNode(true));
        [...pagination.children].forEach((bullet, index) => {
          bullet.dataset.reviewPage = String(index);
          bullet.setAttribute('aria-label', label.replace(/\d+\s*$/, String(index + 1)));
        });
        update();
      };
      this.on(pagination, 'click', event => {
        const bullet = event.target.closest('[data-review-page]');
        if (bullet) show(Number(bullet.dataset.reviewPage));
      });
      this.on(pagination, 'keydown', event => {
        const bullet = event.target.closest('[data-review-page]');
        if (bullet && ['Enter', ' '].includes(event.key)) {
          event.preventDefault();
          show(Number(bullet.dataset.reviewPage));
        }
      });
      this.on(previous, 'click', () => show(current - 1));
      this.on(next, 'click', () => show(current + 1));
      this.on(track, 'scroll', update, { passive: true });
      track.tabIndex = 0;
      this.on(track, 'keydown', event => {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
        event.preventDefault();
        show(event.key === 'Home' ? 0 : event.key === 'End' ? offsets.length - 1 : current + (event.key === 'ArrowRight' ? 1 : -1));
      });
      this.resize(track, layout);
      layout();
    }

    stats() {
      const track = this.root.querySelector('[data-stats-track]');
      const dots = all(this.root, '[data-stats-dot]');
      if (!track || !dots.length) return;
      let current = Math.max(0, dots.findIndex(dot => dot.classList.contains('is-active')));
      const show = index => {
        current = Math.max(0, Math.min(index, dots.length - 1));
        track.style.transform = matchMedia('(max-width:749px)').matches ? `translateX(-${current * 100}%)` : '';
        dots.forEach((dot, position) => {
          dot.classList.toggle('is-active', position === current);
          dot.setAttribute('aria-pressed', String(position === current));
        });
      };
      dots.forEach((dot, index) => this.on(dot, 'click', () => show(index)));
      this.swipe(track, direction => show(current + direction));
      this.on(window, 'resize', () => show(current));
      show(current);
    }

    compare() {
      const scroller = this.root.querySelector('[data-avs-scroller]');
      const dots = all(this.root, '[data-avs-dot]');
      if (!scroller || !dots.length) return;
      const cards = all(scroller, '.avs__card--mobile');
      const offsets = () => cards.map(card => Math.min(card.offsetLeft - cards[0].offsetLeft, Math.max(0, scroller.scrollWidth - scroller.clientWidth)));
      const update = () => {
        const positions = offsets();
        const current = positions.reduce((closest, offset, index) => Math.abs(offset - scroller.scrollLeft) < Math.abs(positions[closest] - scroller.scrollLeft) ? index : closest, 0);
        dots.forEach((dot, index) => {
          dot.classList.toggle('is-active', index === current);
          dot.setAttribute('aria-pressed', String(index === current));
        });
      };
      dots.forEach((dot, index) => this.on(dot, 'click', () => scroller.scrollTo({ left: offsets()[index] || 0, behavior: scrollBehavior() })));
      this.on(scroller, 'scroll', update, { passive: true });
      this.resize(scroller, update);
      update();
    }

    dialogs() {
      all(this.root, 'dialog').forEach(dialog => {
        if (dialog.hasAttribute('data-csi-modal')) dialog.hidden = !dialog.open;
        this.on(dialog, 'click', event => {
          if (event.target.closest('[data-csi-close],[data-close]')) window.AurivitaDialog.close(dialog);
          if (event.target !== dialog) return;
          const box = dialog.getBoundingClientRect();
          if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) window.AurivitaDialog.close(dialog);
        });
        this.on(dialog, 'close', () => {
          if (dialog.open) return;
          if (dialog.hasAttribute('data-csi-modal')) dialog.hidden = true;
          document.body.classList.toggle('av-modal-open', !!document.querySelector('dialog[open]'));
          all(dialog, 'video').forEach(video => video.pause());
          const origin = focusOrigins.get(dialog);
          if (origin?.isConnected) origin.focus({ preventScroll: true });
          focusOrigins.delete(dialog);
        }, { capture: true });
      });
    }
  }

  function initVideos(root) {
    const owned = [];
    let observer;
    if (typeof IntersectionObserver !== 'undefined') {
      observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
          if (!entry.isIntersecting) return;
          entry.target.poster = entry.target.dataset.fwPoster;
          observer.unobserve(entry.target);
        });
      }, { rootMargin: '200px' });
    }
    all(root, 'video').forEach(video => {
      if (videoInstances.has(video)) return;
      videoInstances.add(video);
      owned.push(video);
      if (video.dataset.fwPoster) {
        if (observer) observer.observe(video);
        else video.poster = video.dataset.fwPoster;
      }
    });
    return () => {
      observer?.disconnect();
      owned.forEach(video => {
        videoInstances.delete(video);
        video.pause();
      });
    };
  }

  function init(root = document) {
    const roots = root.matches?.(selector) ? [root, ...all(root, selector)] : all(root, selector);
    roots.forEach(section => {
      if (!instances.has(section)) instances.set(section, new ContentSection(section));
    });
  }

  function destroy(root) {
    const roots = root.matches?.(selector) ? [root, ...all(root, selector)] : all(root, selector);
    roots.forEach(section => {
      instances.get(section)?.destroy();
      instances.delete(section);
    });
  }

  document.addEventListener('click', async event => {
    const opener = event.target.closest('[data-csi-open-target]');
    if (!opener) return;
    const requested = opener.dataset.csiOpenTarget;
    const dialog = (requested ? document.getElementById(requested) : null)
      || opener.closest(selector)?.querySelector('dialog[data-csi-modal]')
      || document.querySelector(`${selector} dialog[data-csi-modal]`);
    if (!dialog || dialog.open) return;
    event.preventDefault();
    const menu = opener.closest('dialog');
    const returnTo = menu ? document.querySelector(`[data-open="${CSS.escape(menu.id)}"]`) || opener : opener;
    focusOrigins.set(dialog, returnTo);
    if (menu?.id === 'av-menu-dialog' && window.AurivitaMenu) {
      if (!await window.AurivitaMenu.close()) return;
    } else if (menu) await window.AurivitaDialog.close(menu);
    if (!dialog.isConnected || dialog.open) return;
    window.AurivitaDialog.open(dialog);
    document.body.classList.add('av-modal-open');
  });
  document.addEventListener('av:content-refresh', event => {
    const root = event.detail?.root || document;
    destroy(root);
    init(root);
  });
  window.AurivitaContent = { init, destroy, initVideos };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => init(), { once: true });
  else init();
})();
