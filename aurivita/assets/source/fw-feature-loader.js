const features = new Map([
  ['variant-picker', 'variant-picker.js'],
  ['product-card,product-card-link,swatches-variant-picker-component', 'product-card.js'],
  ['product-form-component,add-to-cart-component', 'product-form.js'],
  ['fly-to-cart', 'fly-to-cart.js'],
  ['accordion-custom', 'accordion-custom.js'],
  ['deferred-media,product-model', 'media.js'],
  ['product-price', 'product-price.js'],
  ['product-sku-component', 'product-sku.js'],
  ['product-inventory', 'product-inventory.js'],
  ['show-more-component', 'show-more.js'],
  ['slideshow-component', 'slideshow.js'],
  ['layered-slideshow-component', 'layered-slideshow.js'],
  ['anchored-popover-component', 'anchored-popover.js'],
  ['floating-panel-component', 'floating-panel.js'],
  ['video-background-component', 'video-background.js'],
  ['media-gallery', 'media-gallery.js'],
  ['volume-pricing', 'volume-pricing.js'],
  ['price-per-item', 'price-per-item.js'],
]);
const loading = new Map();
function scan(root) {
  for (const [selector, filename] of features) {
    if (loading.has(filename) || !(root.matches?.(selector) || root.querySelector?.(selector))) continue;
    const moduleUrl = `@theme/${filename.slice(0, -3)}`;
    loading.set(filename, import(moduleUrl).catch(error => { loading.delete(filename); console.error(`Theme feature ${filename}:`, error); }));
  }
}
scan(document);
new MutationObserver(records => {
  for (const record of records) for (const node of record.addedNodes) if (node.nodeType === Node.ELEMENT_NODE) scan(node);
}).observe(document.body, { childList: true, subtree: true });
