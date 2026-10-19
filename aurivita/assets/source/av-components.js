import { Component } from '@theme/component';

export class AurivitaSection extends Component {
  connectedCallback() {
    super.connectedCallback();
    this.controller?.abort();
    this.controller = new AbortController();
    this.bindControls();
  }
  updatedCallback() {
    super.updatedCallback();
    this.controller?.abort();
    this.controller = new AbortController();
    this.bindControls();
  }
  disconnectedCallback() {
    this.controller?.abort();
    this.observer?.disconnect();
    this.querySelectorAll('video').forEach(video => video.pause());
    super.disconnectedCallback();
  }
  on(target, type, handler, options = {}) {
    target?.addEventListener(type, handler, { ...options, signal: this.controller.signal });
  }
  all(selector) { return [...this.querySelectorAll(selector)]; }
  bindControls() {
    this.observer?.disconnect();
    this.observer = new IntersectionObserver(entries => {
      for (const entry of entries) {
        if (!entry.isIntersecting) continue;
        const video = entry.target;
        video.poster = video.dataset.fwPoster;
        this.observer.unobserve(video);
      }
    }, { rootMargin: '200px' });
    this.all('video[data-fw-poster]').forEach(video => this.observer.observe(video));
    this.all('.cc-pdp-faqs__toggle,.cc-faq__toggle,[data-csi-accordion]').forEach(toggle => {
      const identifier = toggle.getAttribute('aria-controls');
      const panel = identifier ? this.querySelector('#' + CSS.escape(identifier)) : toggle.nextElementSibling;
      if (panel) panel.hidden = toggle.getAttribute('aria-expanded') !== 'true';
    });
    const slides = this.all('.cc-pdp__main-slide');
    const thumbs = this.all('.cc-pdp__thumb');
    if (slides.length) {
      this.gallery = 0;
      this.selectImage = index => {
        this.gallery = Math.max(0, Math.min(index, slides.length - 1));
        slides.forEach((slide, current) => { slide.hidden = current !== this.gallery; });
        thumbs.forEach((thumb, current) => {
          const active = current === this.gallery;
          thumb.classList.toggle('swiper-slide-thumb-active', active);
          thumb.setAttribute('aria-pressed', String(active));
        });
        const previous = this.querySelector('.cc-pdp__main-arrow--prev');
        const next = this.querySelector('.cc-pdp__main-arrow--next');
        if (previous) previous.disabled = this.gallery === 0;
        if (next) next.disabled = this.gallery === slides.length - 1;
        previous?.setAttribute('aria-disabled', String(previous.disabled));
        next?.setAttribute('aria-disabled', String(next.disabled));
        const peek = this.querySelector('.gallery-peek');
        if (peek) {
          peek.hidden = this.gallery === slides.length - 1;
          const image = slides[Math.min(this.gallery + 1, slides.length - 1)].querySelector('img');
          const preview = peek.querySelector('img');
          if (image && preview) {
            preview.srcset = image.srcset;
            preview.sizes = image.sizes;
            preview.loading = 'lazy';
            preview.fetchPriority = 'low';
            preview.src = image.src;
          }
        }
        const wrap = this.querySelector('.cc-pdp__thumbs-wrap,.fw-product__thumbs');
        if (wrap && thumbs[this.gallery] && matchMedia('(min-width:750px)').matches) wrap.scrollTop = Math.max(0, thumbs[this.gallery].offsetTop - wrap.clientHeight / 2 + 55);
        else if (wrap && thumbs[this.gallery]) wrap.scrollLeft = Math.max(0, thumbs[this.gallery].offsetLeft - wrap.clientWidth / 2 + 29);
      };
      thumbs.forEach((thumb, index) => this.on(thumb, 'click', () => this.selectImage(index)));
      this.on(this.querySelector('.cc-pdp__main-arrow--prev'), 'click', () => this.selectImage(this.gallery - 1));
      this.on(this.querySelector('.cc-pdp__main-arrow--next'), 'click', () => this.selectImage(this.gallery + 1));
      const gallery = this.querySelector('.cc-pdp__main-wrap');
      if (gallery) gallery.tabIndex = 0;
      this.on(gallery, 'keydown', event => {
        if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
        event.preventDefault(); this.selectImage(this.gallery + (event.key === 'ArrowRight' ? 1 : -1));
      });
      let start = 0;
      this.on(gallery, 'touchstart', event => { start = event.changedTouches[0].clientX; }, { passive: true });
      this.on(gallery, 'touchend', event => { const delta = event.changedTouches[0].clientX - start; if (Math.abs(delta) > 50) this.selectImage(this.gallery + (delta < 0 ? 1 : -1)); }, { passive: true });
      const initialIndex = slides.findIndex(slide => !slide.hidden);
      this.selectImage(Math.max(0, initialIndex));
    }
    for (const selector of ['.cc-doctors-see', '.cc-timeline']) {
      const region = this.querySelector(selector);
      if (!region) continue;
      const tabs = [...region.querySelectorAll('[role="tab"]')];
      const activate = index => {
        tabs.forEach((tab, current) => {
          const active = current === index;
          tab.classList.toggle('is-active', active); tab.setAttribute('aria-selected', String(active)); tab.tabIndex = active ? 0 : -1;
          const panel = this.querySelector('#' + CSS.escape(tab.getAttribute('aria-controls')));
          if (panel) { panel.hidden = !active; panel.classList.toggle('is-active', active); }
        });
        [...region.querySelectorAll('.swiper-slide')].forEach((slide, current) => { slide.hidden = current !== index; });
      };
      tabs.forEach((tab, index) => {
        this.on(tab, 'click', () => activate(index));
        this.on(tab, 'keydown', event => {
          if (!['ArrowRight', 'ArrowLeft', 'Home', 'End'].includes(event.key)) return;
          event.preventDefault();
          const next = event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1 : (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
          activate(next); tabs[next].focus();
        });
      });
      activate(0);
    }
    this.on(this, 'click', event => {
      const accordion = event.target.closest('.cc-pdp-faqs__toggle,.cc-faq__toggle,[data-csi-accordion]');
      if (accordion) {
        const item = accordion.closest('.cc-pdp-faqs__item,.cc-faq__item,.csi-item');
        const open = !item.classList.contains('is-open'); item.classList.toggle('is-open', open); accordion.setAttribute('aria-expanded', String(open));
        const panelId = accordion.getAttribute('aria-controls');
        const panel = panelId ? this.querySelector('#' + CSS.escape(panelId)) : accordion.nextElementSibling;
        if (panel) { panel.hidden = !open; if (panel.classList.contains('csi-panel')) panel.dataset.open = String(open); }
      }
      const opener = event.target.closest('[data-open]');
      if (opener) { const dialog = this.querySelector('#' + CSS.escape(opener.dataset.open)); if (dialog) { this.dialogFocus = opener; dialog.showModal(); } }
      if (event.target.closest('[data-close],[data-csi-close]')) event.target.closest('dialog')?.close();
      const scienceOpener = event.target.closest('[data-csi-open-target]');
      if (scienceOpener) {
        const dialog = document.querySelector('dialog[data-csi-modal]');
        if (dialog) {
          const menuDialog = scienceOpener.closest('dialog');
          dialog.returnFocus = menuDialog ? this.querySelector('[data-open]') ?? scienceOpener : scienceOpener;
          menuDialog?.close();
          dialog.showModal();
        }
      }
      if (event.target.closest('[data-nav]')) event.target.closest('dialog')?.close();
      const reviewArrow = event.target.closest('.cc-customer-notice__arrow');
      if (reviewArrow) { const track = this.querySelector('.cc-customer-notice__swiper'); track.scrollBy({ left: track.clientWidth * (reviewArrow.classList.contains('cc-customer-notice__arrow--prev') ? -1 : 1), behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' }); }
      const videoSlide = event.target.closest('.cc-pdp__video-slide');
      if (videoSlide) {
        const video = videoSlide.querySelector('video');
        if (event.target.closest('.cc-pdp__video-play')) {
          if (video.dataset.fwPoster) video.poster = video.dataset.fwPoster;
          document.querySelectorAll('video').forEach(other => { if (other !== video) other.pause(); });
          video.muted = false; video.controls = true;
          video.play().then(() => videoSlide.classList.add('is-playing')).catch(() => { video.controls = true; });
        }
        if (event.target.closest('.cc-pdp__video-ctl--pause')) video.pause();
        if (event.target.closest('.cc-pdp__video-ctl--sound')) video.muted = !video.muted;
      }
    });
    this.all('dialog').forEach(dialog => {
      this.on(dialog, 'close', () => (dialog.returnFocus ?? this.dialogFocus)?.focus());
      this.on(dialog, 'click', event => { if (event.target === dialog) { const box = dialog.getBoundingClientRect(); if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) dialog.close(); } });
    });
    this.all('video').forEach(video => {
      const slide = video.closest('.cc-pdp__video-slide');
      const controls = slide?.querySelector('.cc-pdp__video-controls');
      const updateControls = playing => {
        controls?.setAttribute('aria-hidden', String(!playing));
        controls?.querySelectorAll('button').forEach(button => { button.tabIndex = playing ? 0 : -1; });
      };
      const stop = () => { slide?.classList.remove('is-playing'); updateControls(false); };
      this.on(video, 'play', () => updateControls(true));
      this.on(video, 'pause', stop); this.on(video, 'ended', stop);
      updateControls(false);
    });
    const reviews = this.querySelector('.cc-customer-notice__swiper');
    if (reviews) {
      const bullets = this.all('.swiper-pagination-bullet');
      const show = index => reviews.scrollTo({ left: index * reviews.clientWidth, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
      bullets.forEach((bullet, index) => {
        bullet.setAttribute('aria-pressed', String(index === 0));
        this.on(bullet, 'click', () => show(index));
        this.on(bullet, 'keydown', event => { if (['Enter',' '].includes(event.key)) { event.preventDefault(); show(index); } });
      });
      this.on(reviews, 'scroll', () => { const current = Math.round(reviews.scrollLeft / reviews.clientWidth); bullets.forEach((bullet,index) => { bullet.classList.toggle('swiper-pagination-bullet-active', index === current); bullet.setAttribute('aria-pressed', String(index === current)); }); }, { passive: true });
    }
    const statsTrack = this.querySelector('[data-stats-track]');
    const dots = this.all('[data-stats-dot]');
    if (statsTrack) {
      let current = 0;
      const show = index => { current = Math.max(0, Math.min(index, dots.length - 1)); statsTrack.style.transform = matchMedia('(max-width:749px)').matches ? `translateX(-${current * 100}%)` : ''; dots.forEach((dot, i) => { dot.classList.toggle('is-active', i === current); dot.setAttribute('aria-pressed', String(i === current)); }); };
      dots.forEach((dot, index) => this.on(dot, 'click', () => show(index)));
      let start = 0;
      this.on(statsTrack, 'touchstart', event => { start = event.changedTouches[0].clientX; }, { passive: true });
      this.on(statsTrack, 'touchend', event => { const delta = event.changedTouches[0].clientX - start; if (Math.abs(delta) > 50) show(current + (delta < 0 ? 1 : -1)); }, { passive: true });
      this.on(window, 'resize', () => show(current)); show(0);
    }
    const compare = this.querySelector('[data-avs-scroller]');
    if (compare) {
      const dots = this.all('[data-avs-dot]');
      const step = () => compare.querySelector('.avs__card--mobile').offsetWidth + (parseFloat(getComputedStyle(compare.querySelector('.avs__mobile-track')).gap) || 12);
      dots.forEach((dot, index) => {
        dot.setAttribute('aria-pressed', String(index === 0));
        this.on(dot, 'click', () => compare.scrollTo({ left: step() * index, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' }));
      });
      this.on(compare, 'scroll', () => dots.forEach((dot, index) => { const active = index === Math.round(compare.scrollLeft / step()); dot.classList.toggle('is-active', active); dot.setAttribute('aria-pressed', String(active)); }), { passive: true });
    }
  }
}


if (!customElements.get('av-section')) customElements.define('av-section', AurivitaSection);
