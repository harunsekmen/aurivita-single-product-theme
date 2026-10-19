(() => {
  window.AurivitaAccordion = (button, panel, item) => {
    let hideTimer;
    let clip = panel.querySelector(':scope > .av-accordion-clip');
    if (!clip) {
      clip = document.createElement('div');
      clip.className = 'av-accordion-clip';
      clip.append(...panel.childNodes);
      panel.append(clip);
    }
    panel.classList.add('av-accordion-panel');
    const finish = event => {
      if (event && (event.target !== panel || event.propertyName !== 'grid-template-rows')) return;
      if (button.getAttribute('aria-expanded') !== 'true') panel.hidden = true;
      clearTimeout(hideTimer);
    };
    const set = (open, animate = true) => {
      clearTimeout(hideTimer);
      button.setAttribute('aria-expanded', String(open));
      panel.inert = !open;
      const immediate = !animate || matchMedia('(prefers-reduced-motion: reduce)').matches;
      if (open) {
        panel.hidden = false;
        if (!immediate) panel.getBoundingClientRect();
      }
      item?.classList.toggle('is-open', open);
      panel.dataset.avOpen = String(open);
      if (panel.classList.contains('csi-panel')) panel.dataset.open = String(open);
      if (immediate) panel.hidden = !open;
      else if (!open) hideTimer = setTimeout(finish, 360);
    };
    panel.addEventListener('transitionend', finish);
    set(button.getAttribute('aria-expanded') === 'true', false);
    return {
      set,
      destroy() {
        clearTimeout(hideTimer);
        panel.removeEventListener('transitionend', finish);
        panel.hidden = button.getAttribute('aria-expanded') !== 'true';
      },
    };
  };
  const triggers = Array.from(document.querySelectorAll('[data-open], [data-av-cart-open]'));
  const triggerDialog = trigger => document.getElementById(trigger.dataset.open || (trigger.hasAttribute('data-av-cart-open') ? 'av-cart-dialog' : ''));
  const dialogExpanded = (dialog, open) => triggers.filter(trigger => triggerDialog(trigger) === dialog)
    .forEach(trigger => trigger.setAttribute('aria-expanded', String(open)));
  const toggleScroll = () => document.body.classList.toggle('av-modal-open', !!document.querySelector('dialog[open], [data-csi-modal]:not(dialog):not([hidden])'));
  const dialogStates = new WeakMap();
  const dialogState = dialog => {
    if (dialogStates.has(dialog)) return dialogStates.get(dialog);
    const state = {};
    dialogStates.set(dialog, state);
    dialog.addEventListener('close', () => {
      if (dialog.open) return;
      const pending = state.pending;
      clearTimeout(pending?.timer);
      state.pending = null;
      dialog.removeAttribute('data-closing');
      dialog.inert = false;
      dialogExpanded(dialog, false);
      toggleScroll();
      pending?.resolve(true);
    });
    dialog.addEventListener('animationend', event => {
      if (event.target === dialog && !event.pseudoElement && state.pending?.names.includes(event.animationName)) dialog.close();
    });
    return state;
  };
  window.AurivitaDialog = {
    open(dialog) {
      if (!dialog) return;
      const state = dialogState(dialog);
      if (state.pending && !dialog.open) return state.pending.promise.then(() => this.open(dialog));
      if (state.pending) {
        clearTimeout(state.pending.timer);
        state.pending.resolve(false);
        state.pending = null;
      }
      dialog.removeAttribute('data-closing');
      dialog.inert = false;
      dialog.hidden = false;
      if (!dialog.open) dialog.showModal();
      dialogExpanded(dialog, true);
      toggleScroll();
    },
    close(dialog) {
      if (!dialog?.open) return Promise.resolve(true);
      const state = dialogState(dialog);
      if (state.pending) return state.pending.promise;
      const pending = {};
      pending.promise = new Promise(resolve => { pending.resolve = resolve; });
      state.pending = pending;
      dialogExpanded(dialog, false);
      dialog.inert = true;
      dialog.setAttribute('data-closing', '');
      pending.names = getComputedStyle(dialog).animationName.split(',').map(name => name.trim());
      if (matchMedia('(prefers-reduced-motion: reduce)').matches || pending.names.every(name => name === 'none')) dialog.close();
      else pending.timer = setTimeout(() => { if (state.pending === pending) dialog.close(); }, 340);
      return pending.promise;
    },
  };
  const menu = document.getElementById('av-menu-dialog');
  let menuOrigin;
  let menuClosing;
  const menuExpanded = open => dialogExpanded(menu, open);
  const openMenu = trigger => {
    if (!menu) return;
    if (menuClosing) {
      clearTimeout(menuClosing.timer);
      menuClosing.resolve(false);
      menuClosing = null;
    }
    if (!menu.open) menuOrigin = trigger || document.activeElement;
    menu.removeAttribute('data-closing');
    menu.inert = false;
    document.body.classList.add('av-menu-open');
    if (!menu.open) {
      menu.querySelector('nav')?.scrollTo(0, 0);
      menu.showModal();
    }
    menuExpanded(true);
    toggleScroll();
  };
  const closeMenu = () => {
    if (!menu?.open) return Promise.resolve(true);
    if (menuClosing) return menuClosing.promise;
    const pending = {};
    pending.promise = new Promise(resolve => { pending.resolve = resolve; });
    menuClosing = pending;
    menuExpanded(false);
    menu.inert = true;
    menu.setAttribute('data-closing', '');
    if (matchMedia('(prefers-reduced-motion: reduce)').matches) menu.close();
    else pending.timer = setTimeout(() => { if (menuClosing === pending) menu.close(); }, 260);
    return pending.promise;
  };
  const requestClose = dialog => {
    if (dialog === menu) return closeMenu();
    return window.AurivitaDialog.close(dialog);
  };
  if (menu) {
    window.AurivitaMenu = { open: openMenu, close: closeMenu };
    menu.addEventListener('cancel', event => { event.preventDefault(); closeMenu(); });
    menu.addEventListener('animationend', event => {
      if (event.target === menu && event.animationName === 'av-menu-exit' && menuClosing && menu.hasAttribute('data-closing')) menu.close();
    });
    menu.addEventListener('close', () => {
      if (menu.open) return;
      const pending = menuClosing;
      clearTimeout(pending?.timer);
      menuClosing = null;
      menu.removeAttribute('data-closing');
      menu.inert = false;
      menuExpanded(false);
      document.body.classList.remove('av-menu-open');
      toggleScroll();
      if (!document.querySelector('dialog[open]') && menuOrigin?.isConnected) menuOrigin.focus({ preventScroll: true });
      pending?.resolve(true);
    });
  }
  for (const trigger of triggers) {
    const dialog = triggerDialog(trigger);
    if (!(dialog instanceof HTMLDialogElement)) continue;
    trigger.setAttribute('aria-controls', dialog.id);
    trigger.setAttribute('aria-haspopup', 'dialog');
    trigger.setAttribute('aria-expanded', String(dialog.open));
  }
  document.addEventListener('click', event => {
    const open = event.target.closest('[data-open]');
    if (open) { const dialog = document.getElementById(open.dataset.open); if (dialog instanceof HTMLDialogElement) { event.preventDefault(); if (dialog === menu) openMenu(open); else window.AurivitaDialog.open(dialog); } }
    const close = event.target.closest('[data-close]');
    if (close) requestClose(close.closest('dialog'));
    const nav = event.target.closest('[data-nav]');
    if (nav?.closest('dialog') === menu) {
      if (nav.hasAttribute('data-csi-open-target') && window.AurivitaContent && document.querySelector('dialog[data-csi-modal]')) return;
      const followHere = nav.tagName === 'A' && !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey
        && !nav.hasAttribute('download') && (!nav.target || nav.target === '_self');
      if (followHere) {
        event.preventDefault();
        const destination = new URL(nav.href);
        if (destination.hash && !destination.search && destination.origin === location.origin && destination.pathname === location.pathname) {
          destination.search = location.search;
        }
        closeMenu().then(closed => { if (closed) window.location.assign(destination.href); });
      } else closeMenu();
    } else if (nav) requestClose(nav.closest('dialog'));
  });
  document.querySelectorAll('dialog').forEach(dialog => {
    if (dialog !== menu) dialog.addEventListener('cancel', event => { event.preventDefault(); requestClose(dialog); });
    dialog.addEventListener('close', () => {
      if (dialog.open) return;
      dialogExpanded(dialog, false);
      toggleScroll();
    });
    dialog.addEventListener('click', event => { if (event.target === dialog) { const r=dialog.getBoundingClientRect(); if(event.clientX<r.left || event.clientX>r.right || event.clientY<r.top || event.clientY>r.bottom) requestClose(dialog); } });
  });
})();
