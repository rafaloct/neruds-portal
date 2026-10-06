(function (Drupal, drupalSettings, once) {
  function drawSparkline(element, values) {
    const points = values.map(Number);
    if (!points.length) return;
    const max = Math.max(...points, 1);
    const width = 96;
    const height = 28;
    const step = width / Math.max(points.length - 1, 1);
    const path = points.map((value, index) => {
      const x = index * step;
      const y = height - (value / max) * height;
      return `${index === 0 ? 'M' : 'L'}${x.toFixed(1)},${y.toFixed(1)}`;
    }).join(' ');
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
    svg.setAttribute('focusable', 'false');
    svg.setAttribute('aria-hidden', 'true');
    const svgPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    svgPath.setAttribute('d', path);
    svg.append(svgPath);
    element.replaceChildren(svg);
  }

  Drupal.behaviors.nerudsLiveDashboard = {
    attach(context) {
      once('neruds-live-dashboard', '[data-neruds-live-dashboard]', context).forEach((dashboard) => {
        dashboard.querySelectorAll('[data-sparkline]').forEach((sparkline) => {
          drawSparkline(sparkline, sparkline.dataset.sparkline.split(','));
        });

        dashboard.addEventListener('click', (event) => {
          const card = event.target.closest('[data-metric-id]');
          if (card && typeof window.gtag === 'function') {
            window.gtag('event', 'neruds_dashboard_metric_click', {
              metric_id: card.dataset.metricId,
              link_url: card.href,
            });
          }
        });

        const animate = () => {
          dashboard.querySelectorAll('[data-count-to]').forEach((valueElement) => {
            const value = Number(valueElement.dataset.countTo || 0);
            if (window.countUp?.CountUp) {
              new window.countUp.CountUp(valueElement, value, { duration: 2 }).start();
            }
            else {
              valueElement.textContent = String(value);
            }
          });
        };

        if ('IntersectionObserver' in window) {
          const observer = new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
              animate();
              observer.disconnect();
            }
          }, { threshold: 0.25 });
          observer.observe(dashboard);
        }
        else {
          animate();
        }

        const endpoint = drupalSettings.nerudsLiveDashboard?.endpoint;
        if (endpoint) {
          window.setInterval(() => {
            fetch(endpoint).then((response) => response.json()).then((data) => {
              (data.metrics || []).forEach((metric) => {
                const card = dashboard.querySelector(`[data-metric-id="${metric.id}"]`);
                const value = card?.querySelector('[data-count-to]');
                if (value) {
                  value.dataset.countTo = metric.value;
                  value.textContent = metric.value;
                  card.setAttribute('aria-label', `${metric.label}: ${metric.value}. Abrir detalhamento.`);
                }
              });
            });
          }, 300000);
        }
      });
    },
  };
})(Drupal, drupalSettings, once);
