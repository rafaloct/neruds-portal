(function (Drupal, once) {
  function normalize(value) {
    return value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
  }

  function badgeValues(card, selector) {
    const badge = card.querySelector(selector);
    if (!badge) {
      return [];
    }

    // Taxonomy formatters can render several terms as links or field items.
    // Reading only textContent joins those terms together and makes a selected
    // term impossible to match on the homepage.
    const items = Array.from(badge.querySelectorAll('a, .field__item'));
    const labels = items.length
      ? items.map((item) => item.textContent.trim())
      : badge.textContent.split(/[,;|]/).map((value) => value.trim());

    return [...new Map(labels
      .filter(Boolean)
      .map((label) => [normalize(label), label]))
      .entries()];
  }

  function buildFilters(view) {
    const cards = Array.from(view.querySelectorAll('.neruds-news-card'));
    if (!cards.length || view.querySelector('[data-news-filterbar]')) {
      return;
    }

    const territories = new Map();
    const ods = new Map();
    cards.forEach((card) => {
      const territoryValues = badgeValues(card, '.neruds-news-card__badge--territory');
      const odsValues = badgeValues(card, '.neruds-news-card__badge--ods');
      territoryValues.forEach(([value, label]) => territories.set(value, label));
      odsValues.forEach(([value, label]) => ods.set(value, label));
      card.dataset.territoryFilter = territoryValues.map(([value]) => value).join('|');
      card.dataset.odsFilter = odsValues.map(([value]) => value).join('|');
    });

    const bar = document.createElement('div');
    bar.className = 'neruds-news-filterbar';
    bar.dataset.newsFilterbar = 'true';
    bar.innerHTML = `
      <label>Territorio
        <select data-news-filter="territory">
          <option value="">Todos</option>
          ${Array.from(territories, ([value, label]) => `<option value="${value}">${label}</option>`).join('')}
        </select>
      </label>
      <label>ODS
        <select data-news-filter="ods">
          <option value="">Todos</option>
          ${Array.from(ods, ([value, label]) => `<option value="${value}">${label}</option>`).join('')}
        </select>
      </label>
      <button type="button" data-news-filter-reset>Limpar</button>
    `;
    view.prepend(bar);

    const apply = () => {
      const territory = bar.querySelector('[data-news-filter="territory"]').value;
      const selectedOds = bar.querySelector('[data-news-filter="ods"]').value;
      cards.forEach((card) => {
        const territoryMatch = !territory || card.dataset.territoryFilter.split('|').includes(territory);
        const odsMatch = !selectedOds || card.dataset.odsFilter.split('|').includes(selectedOds);
        card.hidden = !(territoryMatch && odsMatch);
      });
      if (typeof window.gtag === 'function') {
        window.gtag('event', 'neruds_news_filter', {
          territory,
          ods: selectedOds,
        });
      }
    };

    bar.addEventListener('change', apply);
    bar.querySelector('[data-news-filter-reset]').addEventListener('click', () => {
      bar.querySelectorAll('select').forEach((select) => { select.value = ''; });
      apply();
    });
  }

  Drupal.behaviors.nerudsTerritorialNews = {
    attach(context) {
      once('neruds-territorial-news-filterbar', '.view-neruds-noticias, [data-drupal-selector="views-exposed-form-neruds-noticias-page-1"]', context).forEach((view) => {
        const container = view.classList.contains('view-neruds-noticias') ? view : view.closest('.view-neruds-noticias') || document;
        buildFilters(container);
      });

      once('neruds-territorial-news', '.neruds-news-card', context).forEach((card) => {
        const share = card.querySelector('[data-news-share]');
        const expand = card.querySelector('[data-news-expand]');
        if (expand) {
          expand.addEventListener('click', () => {
            const shortSummary = card.querySelector('[data-news-summary-short]');
            const fullSummary = card.querySelector('[data-news-summary-full]');
            const expanded = expand.getAttribute('aria-expanded') === 'true';
            if (shortSummary && fullSummary) {
              shortSummary.hidden = !expanded;
              fullSummary.hidden = expanded;
              expand.textContent = expanded ? 'Leia mais' : 'Recolher';
              expand.setAttribute('aria-expanded', String(!expanded));
            }
          });
        }
        if (share && navigator.share) {
          share.hidden = false;
          share.addEventListener('click', () => {
            if (typeof window.gtag === 'function') {
              window.gtag('event', 'neruds_news_share', {
                content_id: card.dataset.nid,
              });
            }
            navigator.share({
              title: card.querySelector('h3')?.textContent || document.title,
              url: card.querySelector('a')?.href || window.location.href,
            });
          });
        }
      });
    },
  };
})(Drupal, once);
