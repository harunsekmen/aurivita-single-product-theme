document.addEventListener('click', async event => {
  const link = event.target.closest('[data-av-privacy-preferences]');
  if (!link || typeof window.privacyBanner?.showPreferences !== 'function') return;
  event.preventDefault();
  try {
    await window.privacyBanner.showPreferences();
  } catch {
    window.location.assign(link.href);
  }
});
