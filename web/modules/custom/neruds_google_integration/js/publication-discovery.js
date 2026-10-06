(function (Drupal, drupalSettings, once) {
  const SEARCH_MODE = {
    NERUDS: 'neruds',
    SCIENTIFIC: 'scientific',
  };

  function text(value) {
    return value ? String(value) : '';
  }

  function formatNumber(value) {
    if (value === null || value === undefined || value === '') return '0';
    return Number(value).toLocaleString('pt-BR');
  }

  function renderResult(item, mode) {
    const article = document.createElement('article');
    article.className = 'neruds-publication-discovery__card';

    const heading = document.createElement('h3');
    const link = document.createElement('a');
    link.href = text(item.link);
    link.textContent = text(item.title);
    heading.append(link);

    const meta = document.createElement('p');
    meta.className = 'neruds-publication-discovery__meta';
    if (mode === SEARCH_MODE.SCIENTIFIC) {
      const parts = [];
      if (item.authors) parts.push(text(item.authors));
      if (item.journal) parts.push(text(item.journal));
      if (item.published_at) parts.push(text(item.published_at));
      if (item.cited_by !== undefined && item.cited_by !== null) parts.push(`Cited by ${text(item.cited_by)}`);
      meta.textContent = parts.join(' | ');
    }
    else {
      meta.textContent = text(item.displayLink || '');
    }

    const snippet = document.createElement('p');
    snippet.textContent = text(item.snippet);

    const actions = document.createElement('div');
    actions.className = 'neruds-publication-discovery__actions';
    const open = document.createElement('a');
    open.href = text(item.link);
    open.textContent = 'Abrir';
    const cite = document.createElement('button');
    cite.type = 'button';
    cite.dataset.citeTitle = text(item.title);
    cite.dataset.citeUrl = text(item.link);
    cite.textContent = 'Citar';
    actions.append(open, cite);

    article.append(heading, meta, snippet, actions);
    return article;
  }

  function renderScientificSummary(container, data) {
    if (!container) return;
    const returned = (data.items || []).length;
    const total = Number(data.search_total || 0);
    const start = Number(data.start || 0);
    const provider = data.provider || 'elsevier_scopus_api';
    const query = data.query || '';

    const summary = document.createElement('div');
    summary.className = 'neruds-publication-discovery__summary-grid';

    const cards = [
      ['Resultados retornados', formatNumber(returned)],
      ['Total na base Scopus', formatNumber(total)],
      ['Inicio da janela', formatNumber(start + 1)],
      ['Provedor', provider],
    ];

    cards.forEach(([label, value]) => {
      const card = document.createElement('section');
      card.className = 'neruds-publication-discovery__metric';
      const strong = document.createElement('strong');
      strong.textContent = value;
      const span = document.createElement('span');
      span.textContent = label;
      card.append(strong, span);
      summary.append(card);
    });

    const caption = document.createElement('p');
    caption.className = 'neruds-publication-discovery__summary-caption';
    caption.textContent = query
      ? `Consulta: "${query}". Resultados academicos via Scopus API.`
      : 'Digite um termo para consultar literatura global (Scopus).';

    container.replaceChildren(summary, caption);
  }

  function renderDrupalSummary(container, data) {
    if (!container) return;
    const metrics = data.metrics || data;
    const provider = data.provider || 'Drupal';
    const googleTotal = data.search_total;
    const filtered = metrics.filtered_total ?? metrics.matched_total ?? metrics.total ?? 0;
    const query = data.query || metrics.query || '';
    const filters = metrics.filters || {};
    const hasQuery = text(query).trim() !== '';
    const hasActiveFilters = ['territory', 'ods', 'type', 'year']
      .some((key) => text(filters[key]).trim() !== '');
    const hasActiveScope = hasQuery || hasActiveFilters;
    const relatedLabel = hasActiveScope
      ? 'Publicacoes relacionadas no Drupal'
      : 'Publicacoes relacionadas no Drupal (aguardando consulta)';
    const relatedValue = hasActiveScope ? formatNumber(filtered) : '-';

    const summary = document.createElement('div');
    summary.className = 'neruds-publication-discovery__summary-grid';

    const cards = [
      ['Resultados no escopo Google', googleTotal === null || googleTotal === undefined ? 'n/d' : formatNumber(googleTotal)],
      [relatedLabel, relatedValue],
      ['Base editorial de publicacoes', formatNumber(metrics.total || 0)],
      ['Provedor', provider],
    ];

    cards.forEach(([label, value]) => {
      const card = document.createElement('section');
      card.className = 'neruds-publication-discovery__metric';
      const strong = document.createElement('strong');
      strong.textContent = value;
      const span = document.createElement('span');
      span.textContent = label;
      card.append(strong, span);
      summary.append(card);
    });

    const caption = document.createElement('p');
    caption.className = 'neruds-publication-discovery__summary-caption';
    caption.textContent = query
      ? `Consulta: "${query}". Escopo: ${data.scope || 'publicacoes NERUDS'}.`
      : 'Digite um termo para cruzar busca publica e metadados editoriais.';

    container.replaceChildren(summary, caption);
  }

  function renderSummary(container, data, mode) {
    if (mode === SEARCH_MODE.SCIENTIFIC) {
      renderScientificSummary(container, data);
      return;
    }
    renderDrupalSummary(container, data);
  }

  function renderFacets(container, data, currentQuery, mode) {
    if (!container) return;
    if (mode === SEARCH_MODE.SCIENTIFIC) {
      const title = document.createElement('h3');
      title.textContent = 'Filtros';
      const content = document.createElement('p');
      content.textContent = 'Neste modo, use termos no campo de busca (tema, autor, revista, DOI).';
      container.replaceChildren(title, content);
      return;
    }

    const metrics = data.metrics || data;
    const facets = metrics.facets || {};
    const labels = {
      territories: 'Territorios',
      ods: 'ODS',
      types: 'Tipos',
      years: 'Anos',
    };

    const heading = document.createElement('h3');
    heading.textContent = 'Filtros e contagens';
    const fragments = [heading];

    Object.keys(labels).forEach((key) => {
      const values = facets[key] || [];
      if (!values.length) return;
      const group = document.createElement('section');
      group.className = 'neruds-publication-discovery__facet-group';
      const title = document.createElement('h4');
      title.textContent = labels[key];
      const list = document.createElement('div');
      list.className = 'neruds-publication-discovery__facet-list';

      values.slice(0, 10).forEach((facet) => {
        const link = document.createElement('a');
        // Preserve selected facets: clicking a second facet must not silently
        // discard the filters already represented in the current URL.
        const params = new URLSearchParams(window.location.search);
        if (currentQuery) params.set('q', currentQuery);
        params.set('source', SEARCH_MODE.NERUDS);
        const filterName = key === 'territories' ? 'territory' : key === 'types' ? 'type' : key === 'years' ? 'year' : 'ods';
        params.set(filterName, facet.value);
        link.href = `/publicacoes-discovery?${params.toString()}`;
        link.textContent = `${facet.label} (${facet.count})`;
        list.append(link);
      });

      group.append(title, list);
      fragments.push(group);
    });

    if (fragments.length === 1) {
      const empty = document.createElement('p');
      empty.textContent = 'Sem facetas para esta consulta.';
      fragments.push(empty);
    }

    container.replaceChildren(...fragments);
  }

  function renderSkeletonCard() {
    const skeleton = document.createElement('div');
    skeleton.className = 'neruds-skeleton-card neruds-skeleton';
    skeleton.innerHTML = `
      <div class="neruds-skeleton-heading neruds-skeleton"></div>
      <div class="neruds-skeleton-snippet neruds-skeleton"></div>
      <div class="neruds-skeleton-snippet neruds-skeleton"></div>
      <div class="neruds-skeleton-actions">
        <div class="neruds-skeleton"></div>
        <div class="neruds-skeleton"></div>
      </div>
    `;
    return skeleton;
  }

  function renderSkeletonGrid(count = 5) {
    const container = document.createElement('div');
    container.className = 'neruds-skeleton-grid';
    for (let i = 0; i < count; i++) {
      container.append(renderSkeletonCard());
    }
    return container;
  }

  function renderResults(container, data, mode) {
    const items = data.items || [];
    if (!items.length) {
      const message = document.createElement('p');
      message.textContent = data.notice || data.error || 'Nenhum resultado encontrado.';
      container.replaceChildren(message);
      return;
    }
    container.replaceChildren(...items.map((item) => renderResult(item, mode)));
  }

  Drupal.behaviors.nerudsPublicationDiscovery = {
    attach(context) {
      once('neruds-publication-discovery', '[data-neruds-publication-discovery]', context).forEach((element) => {
        const form = element.querySelector('[data-publication-search-form]');
        const results = element.querySelector('[data-publication-results]');
        const summary = element.querySelector('[data-publication-summary]');
        const facets = element.querySelector('[data-publication-facets]');
        const sourceButtons = Array.from(element.querySelectorAll('[data-search-source]'));
        const sourceStatus = element.querySelector('[data-source-status]');
        const endpoint = drupalSettings.nerudsPublicationDiscovery?.searchEndpoint || '/neruds/api/google-search';
        const metricsEndpoint = drupalSettings.nerudsPublicationDiscovery?.metricsEndpoint || '/neruds/api/publication-metrics';
        const scientificEndpoint = drupalSettings.nerudsPublicationDiscovery?.scientificEndpoint || '/neruds/api/scientific-search';
        const cseCx = drupalSettings.nerudsPublicationDiscovery?.cseCx;
        const defaultSource = drupalSettings.nerudsPublicationDiscovery?.defaultSource || SEARCH_MODE.NERUDS;
        const csePanel = element.querySelector('[data-google-cse]');
        const params = new URLSearchParams(window.location.search);
        const initialQuery = params.get('q') || '';
        const initialSource = params.get('source') || defaultSource;
        const fetcher = typeof window.fetchWithRetry === 'function'
          ? window.fetchWithRetry.bind(window)
          : window.fetch.bind(window);

        let searchMode = initialSource === SEARCH_MODE.SCIENTIFIC ? SEARCH_MODE.SCIENTIFIC : SEARCH_MODE.NERUDS;

        if (initialQuery) {
          form.querySelector('[name="q"]').value = initialQuery;
        }

        const updateModeUi = () => {
          element.dataset.searchMode = searchMode;
          sourceButtons.forEach((button) => {
            const active = button.dataset.searchSource === searchMode;
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
            button.classList.toggle('is-active', active);
          });
          if (sourceStatus) {
            sourceStatus.textContent = searchMode === SEARCH_MODE.SCIENTIFIC
              ? 'Modo: Literatura Global (Scopus)'
              : 'Modo: Acervo NERUDS';
          }
          if (csePanel) {
            const hasDrupalFacet = ['territory', 'ods', 'type', 'year']
              .some((key) => params.get(key));
            // Google CSE does not receive Drupal taxonomy facets, so showing
            // it beside a filtered editorial list is misleading.
            csePanel.hidden = !(searchMode === SEARCH_MODE.NERUDS && cseCx && !hasDrupalFacet);
          }
        };

        const ensureCseScript = () => {
          if (!cseCx || !csePanel || document.querySelector(`script[data-neruds-cse="${cseCx}"]`)) {
            return;
          }
          const script = document.createElement('script');
          script.async = true;
          script.src = `https://cse.google.com/cse.js?cx=${encodeURIComponent(cseCx)}`;
          script.dataset.nerudsCse = cseCx;
          document.head.appendChild(script);
        };

        const runSearch = (query) => {
          const normalizedQuery = text(query).trim();
          if (!normalizedQuery) {
            const empty = document.createElement('p');
            empty.textContent = 'Digite um termo para iniciar a busca.';
            results.replaceChildren(empty);
            return;
          }

          const requestParams = new URLSearchParams(window.location.search);
          requestParams.set('q', normalizedQuery);
          requestParams.set('source', searchMode);

          let requestUrl = '';
          if (searchMode === SEARCH_MODE.SCIENTIFIC) {
            ['territory', 'ods', 'type', 'year', 'start'].forEach((key) => requestParams.delete(key));
            requestUrl = `${scientificEndpoint}?q=${encodeURIComponent(normalizedQuery)}&start=0&count=10`;
          }
          else {
            requestUrl = `${endpoint}?${requestParams.toString()}`;
          }

          window.history.replaceState({}, '', `/publicacoes-discovery?${requestParams.toString()}`);
          results.replaceChildren(renderSkeletonGrid(5));

          fetcher(requestUrl, { headers: { Accept: 'application/json' } })
            .then((response) => response.json())
            .then((data) => {
              if (typeof window.gtag === 'function') {
                window.gtag('event', 'neruds_publication_search', {
                  search_term: normalizedQuery,
                  source_mode: searchMode,
                  provider: data.provider || 'none',
                  result_count: (data.items || []).length,
                  drupal_result_count: data.metrics?.filtered_total || 0,
                  google_result_count: data.search_total || 0,
                });
              }
              renderSummary(summary, data, searchMode);
              renderFacets(facets, data, normalizedQuery, searchMode);
              renderResults(results, data, searchMode);
            })
            .catch((error) => {
              const errorMsg = document.createElement('p');
              errorMsg.textContent = 'Erro ao buscar resultados. Tente novamente.';
              results.replaceChildren(errorMsg);
              console.error('Publication search failed:', error);
            });
        };

        const loadDrupalMetrics = () => {
          const metricsParams = new URLSearchParams(window.location.search);
          metricsParams.set('source', SEARCH_MODE.NERUDS);
          fetcher(`${metricsEndpoint}?${metricsParams.toString()}`, {
            headers: { Accept: 'application/json' },
          })
            .then((response) => response.json())
            .then((data) => {
              if (searchMode === SEARCH_MODE.NERUDS) {
                renderSummary(summary, data, SEARCH_MODE.NERUDS);
                renderFacets(facets, data, initialQuery, SEARCH_MODE.NERUDS);
              }
            })
            .catch((error) => {
              console.error('Metrics fetch failed:', error);
            });
        };

        form.addEventListener('submit', (event) => {
          event.preventDefault();
          const query = text(new FormData(form).get('q')).trim();
          runSearch(query);
        });

        sourceButtons.forEach((button) => {
          button.addEventListener('click', () => {
            const nextMode = button.dataset.searchSource === SEARCH_MODE.SCIENTIFIC
              ? SEARCH_MODE.SCIENTIFIC
              : SEARCH_MODE.NERUDS;
            if (nextMode === searchMode) return;
            searchMode = nextMode;
            updateModeUi();
            if (searchMode === SEARCH_MODE.NERUDS) {
              ensureCseScript();
              loadDrupalMetrics();
            }
            const query = text(new FormData(form).get('q')).trim();
            if (query) {
              runSearch(query);
            }
          });
        });

        updateModeUi();
        ensureCseScript();
        loadDrupalMetrics();

        if (initialQuery) {
          runSearch(initialQuery);
        }
        else if (searchMode === SEARCH_MODE.SCIENTIFIC) {
          renderScientificSummary(summary, {
            items: [],
            search_total: 0,
            provider: 'elsevier_scopus_api',
            query: '',
            start: 0,
          });
          renderFacets(facets, {}, '', SEARCH_MODE.SCIENTIFIC);
        }

        results.addEventListener('click', (event) => {
          const button = event.target.closest('[data-cite-title]');
          if (!button) return;
          const citation = `${button.dataset.citeTitle}. NERUDS. Disponivel em: ${button.dataset.citeUrl}`;
          navigator.clipboard?.writeText(citation)
            .then(() => {
              button.textContent = 'Citacao copiada';
            })
            .catch(() => {
              button.textContent = 'Falha ao copiar';
            });
        });
      });
    },
  };
})(Drupal, drupalSettings, once);
