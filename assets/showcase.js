(() => {
  const root = document.getElementById('ui-kernel-showcase');
  if (!root) {
    return;
  }

  const order = JSON.parse(root.dataset.flavourOrder || '[]');
  const carouselEnabled = root.dataset.carousel === '1';
  const intervalMs = 6000;
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const label = document.getElementById('ui-kernel-theme-label');
  const flavourLabels = {
    default: 'Kiroshi',
    dark: 'Kiroshi dark',
    'bootstrap-like': 'Bootstrap-inspired tokens',
    'bootstrap-like-dark': 'Bootstrap-inspired tokens (dark)',
    'tailwind-like': 'Tailwind-inspired tokens',
    'tailwind-like-dark': 'Tailwind-inspired tokens (dark)',
  };

  let index = Math.max(0, order.indexOf(root.dataset.initialTheme || 'default'));

  const applyTheme = (id, animate) => {
    document.documentElement.setAttribute('data-theme', id);
    if (label) {
      label.textContent = `Current flavour: ${flavourLabels[id] || id}`;
    }
    if (animate && !reducedMotion) {
      root.classList.add('ui-kernel-crossfade');
      root.style.opacity = '0.85';
      window.setTimeout(() => {
        root.style.opacity = '1';
      }, 1000);
    }
  };

  applyTheme(order[index] || 'default', false);

  if (!carouselEnabled || reducedMotion || order.length < 2) {
    return;
  }

  window.setInterval(() => {
    index = (index + 1) % order.length;
    applyTheme(order[index], true);
  }, intervalMs);
})();
