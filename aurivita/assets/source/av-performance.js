const metrics = { lcp: 0, cls: 0, ttfb: 0, load: 0 };
const report = () => {
  const navigation = performance.getEntriesByType('navigation')[0];
  metrics.ttfb = Math.round(navigation?.responseStart ?? 0);
  metrics.load = Math.round(navigation?.loadEventEnd ?? 0);
  document.documentElement.dataset.avPerformance = JSON.stringify(metrics);
};
if (PerformanceObserver.supportedEntryTypes.includes('largest-contentful-paint')) {
  new PerformanceObserver(list => {
    metrics.lcp = Math.round(list.getEntries().at(-1).startTime);
    report();
  }).observe({ type: 'largest-contentful-paint', buffered: true });
}
if (PerformanceObserver.supportedEntryTypes.includes('layout-shift')) {
  let start = 0;
  let last = 0;
  let session = 0;
  new PerformanceObserver(list => {
    for (const entry of list.getEntries()) {
      if (entry.hadRecentInput) continue;
      if (entry.startTime - last < 1000 && entry.startTime - start < 5000) session += entry.value;
      else { start = entry.startTime; session = entry.value; }
      last = entry.startTime;
      metrics.cls = Math.round(Math.max(metrics.cls, session) * 10000) / 10000;
    }
    report();
  }).observe({ type: 'layout-shift', buffered: true });
}
addEventListener('load', () => setTimeout(report, 0), { once: true });
report();
