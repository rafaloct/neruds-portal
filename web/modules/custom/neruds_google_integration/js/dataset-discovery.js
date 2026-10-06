(function (Drupal, once, drupalSettings) {
  const text = (value) => (value === null || value === undefined ? "" : String(value));

  const buildUrl = (endpoint, params) => {
    const url = new URL(endpoint, window.location.origin);
    Object.entries(params).forEach(([key, value]) => {
      if (value !== "" && value !== null && value !== undefined) {
        url.searchParams.set(key, value);
      }
    });
    return url;
  };

  const createElement = (tag, className, content) => {
    const element = document.createElement(tag);
    if (className) {
      element.className = className;
    }
    if (content !== undefined) {
      element.textContent = content;
    }
    return element;
  };

  const formatDate = (value) => {
    if (!value) {
      return "Sem data";
    }
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
      return text(value);
    }
    return date.toLocaleDateString("pt-BR", { day: "2-digit", month: "2-digit", year: "numeric" });
  };

  const setStatus = (state, message) => {
    state.status.textContent = message;
  };

  const renderSummary = (state, data) => {
    state.summary.innerHTML = "";
    [
      ["Datasets", data.total || 0],
      ["Amostras", (data.items || []).reduce((total, item) => total + Number(item.total_amostras || item.total_citacoes || 0), 0)],
      ["Registros", (data.items || []).reduce((total, item) => total + Number(item.total_registros || 0), 0)],
    ].forEach(([label, value]) => {
      const card = createElement("div", "neruds-dataset-discovery__stat");
      card.append(createElement("span", "", label));
      card.append(createElement("strong", "", value));
      state.summary.append(card);
    });
  };

  const populateSelect = (select, rows, selectedValue) => {
    if (!select) {
      return;
    }
    const existingOptions = new Set(Array.from(select.options).map((option) => option.value));
    (rows || []).forEach((row) => {
      const label = text(row.label);
      if (!label || existingOptions.has(label)) {
        return;
      }
      const option = document.createElement("option");
      option.value = label;
      option.textContent = `${label} (${Number(row.count || 0)})`;
      select.append(option);
      existingOptions.add(label);
    });
    if (selectedValue) {
      select.value = selectedValue;
    }
  };

  const renderFacetOptions = (state, data) => {
    const facets = data.facets || {};
    populateSelect(state.project, facets.projetos, state.filters.project);
    populateSelect(state.sigilo, facets.sigilo, state.filters.sigilo);
  };

  const renderDetailTable = (columns, samples) => {
    const wrapper = createElement("div", "neruds-dataset-card__detail-content");

    const columnsTitle = createElement("h3", "", "Colunas disponiveis");
    wrapper.append(columnsTitle);
    if (Array.isArray(columns) && columns.length) {
      const list = createElement("ul", "neruds-dataset-card__column-list");
      columns.forEach((column) => {
        const li = createElement("li", "");
        const name = text(column.column_name || "coluna");
        const type = text(column.data_type || "tipo");
        const sensitivity = text(column.nivel_sensibilidade || "");
        li.textContent = sensitivity
          ? `${name} (${type}) - ${sensitivity}`
          : `${name} (${type})`;
        list.append(li);
      });
      wrapper.append(list);
    } else {
      wrapper.append(createElement("p", "neruds-dataset-card__detail-empty", "Sem metadados publicos de colunas para este dataset."));
    }

    const samplesTitle = createElement("h3", "", "Amostras nao confidenciais");
    wrapper.append(samplesTitle);
    if (Array.isArray(samples) && samples.length) {
      const pre = createElement("pre", "neruds-dataset-card__sample-json");
      pre.textContent = JSON.stringify(samples, null, 2);
      wrapper.append(pre);
    } else {
      wrapper.append(createElement("p", "neruds-dataset-card__detail-empty", "Sem amostras anonimizadas para exibir agora."));
    }

    return wrapper;
  };

  const renderCard = (item, datasetDetailEndpoint, samplesEndpoint) => {
    const card = createElement("article", "neruds-dataset-card");
    const header = createElement("header", "neruds-dataset-card__header");
    const badge = createElement("span", "neruds-dataset-card__badge", text(item.projeto_origem || "NERUDS"));
    const title = createElement("h2", "", text(item.nome_dataset || item.titulo || "Dataset NERUDS"));
    header.append(badge, title);

    const description = createElement("p", "neruds-dataset-card__description", text(item.descricao || "Dataset auditavel."));

    const meta = createElement("dl", "neruds-dataset-card__meta");
    [
      ["Camada", item.camada || item.dataset_fonte],
      ["Tabela", item.tabela_fonte],
      ["Sigilo", item.nivel_sigilo],
      ["Atualizacao", formatDate(item.ultima_ingestao || item.data_mais_recente)],
    ].forEach(([label, value]) => {
      const wrapper = createElement("div", "");
      wrapper.append(createElement("dt", "", label));
      wrapper.append(createElement("dd", "", text(value || "Nao informado")));
      meta.append(wrapper);
    });

    const counts = createElement("div", "neruds-dataset-card__counts");
    counts.append(createElement("span", "", `${Number(item.total_registros || 0)} registros`));
    counts.append(createElement("span", "", `${Number(item.total_amostras || item.total_citacoes || 0)} amostras`));
    if (Number(item.total_colunas || 0) > 0) {
      counts.append(createElement("span", "", `${Number(item.total_colunas)} colunas`));
    }

    const actions = createElement("div", "neruds-dataset-card__actions");
    const samplesLink = createElement("a", "", "Ver amostras");
    const samplesUrl = buildUrl(samplesEndpoint, { project: item.projeto_origem || "" });
    samplesLink.href = samplesUrl.toString();

    const requestLink = createElement("a", "", "Solicitar acesso");
    requestLink.href = `mailto:neruds@uft.edu.br?subject=${encodeURIComponent(`Solicitacao de acesso: ${text(item.nome_dataset || "")}`)}`;

    const detailsButton = createElement("button", "neruds-dataset-card__details-toggle", "Ver colunas e amostras");
    detailsButton.type = "button";
    detailsButton.setAttribute("aria-expanded", "false");

    const detailsContainer = createElement("div", "neruds-dataset-card__details");
    detailsContainer.hidden = true;
    detailsContainer.append(createElement("p", "neruds-dataset-card__detail-empty", "Clique para carregar detalhes do dataset."));

    detailsButton.addEventListener("click", async () => {
      const expanded = detailsButton.getAttribute("aria-expanded") === "true";
      if (expanded) {
        detailsButton.setAttribute("aria-expanded", "false");
        detailsContainer.hidden = true;
        return;
      }
      detailsButton.setAttribute("aria-expanded", "true");
      detailsContainer.hidden = false;

      if (detailsContainer.dataset.loaded === "true") {
        return;
      }

      detailsContainer.innerHTML = "";
      detailsContainer.append(createElement("p", "neruds-dataset-card__detail-empty", "Carregando colunas e amostras..."));

      try {
        const detailUrl = buildUrl(`${datasetDetailEndpoint}/${encodeURIComponent(text(item.id || ""))}`, { sample_limit: "5" });
        const response = await window.fetchWithRetry(detailUrl, { headers: { Accept: "application/json" } });
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }
        const data = await response.json();
        detailsContainer.innerHTML = "";
        detailsContainer.append(renderDetailTable(data.available_columns || [], data.sample_records || []));
        if (data.notice) {
          detailsContainer.append(createElement("p", "neruds-dataset-card__detail-empty", text(data.notice)));
        }
        detailsContainer.dataset.loaded = "true";
      } catch (error) {
        detailsContainer.innerHTML = "";
        detailsContainer.append(createElement("p", "neruds-dataset-card__detail-empty", "Nao foi possivel carregar os detalhes agora."));
      }
    });

    actions.append(samplesLink, requestLink, detailsButton);
    card.append(header, description, meta, counts, actions, detailsContainer);
    return card;
  };

  const renderSkeletonCard = () => {
    const skeleton = createElement("div", "neruds-dataset-card neruds-skeleton");
    skeleton.innerHTML = `
      <div class="neruds-skeleton-heading neruds-skeleton"></div>
      <div class="neruds-skeleton-snippet neruds-skeleton"></div>
      <div class="neruds-skeleton-text neruds-skeleton"></div>
      <div class="neruds-skeleton-text short neruds-skeleton"></div>
      <div class="neruds-skeleton-actions">
        <div class="neruds-skeleton" style="flex: 1;"></div>
        <div class="neruds-skeleton" style="flex: 1;"></div>
      </div>
    `;
    return skeleton;
  };

  const renderSkeletonGrid = (count = 6) => {
    const container = createElement("div", "neruds-dataset-discovery__grid");
    for (let i = 0; i < count; i += 1) {
      container.append(renderSkeletonCard());
    }
    return container;
  };

  const renderResults = (state, data) => {
    state.results.innerHTML = "";
    const items = data.items || [];
    if (!items.length) {
      const empty = createElement("p", "neruds-dataset-discovery__empty", "Nenhum dataset encontrado para os filtros atuais.");
      state.results.append(empty);
      return;
    }
    items.forEach((item) => state.results.append(renderCard(item, state.datasetDetailEndpoint, state.samplesEndpoint)));
  };

  const updateHash = (state) => {
    const hash = new URLSearchParams();
    if (state.filters.q) {
      hash.set("q", state.filters.q);
    }
    if (state.filters.project) {
      hash.set("project", state.filters.project);
    }
    if (state.filters.sigilo) {
      hash.set("sigilo", state.filters.sigilo);
    }
    hash.set("offset", String(state.pagination.offset));
    hash.set("limit", String(state.pagination.limit));
    window.location.hash = hash.toString();
  };

  const readHash = (state) => {
    const hash = window.location.hash.replace(/^#/, "");
    if (!hash) {
      return;
    }
    const params = new URLSearchParams(hash);
    state.filters.q = params.get("q") || "";
    state.filters.project = params.get("project") || "";
    state.filters.sigilo = params.get("sigilo") || "";
    state.pagination.offset = Number.parseInt(params.get("offset") || "0", 10) || 0;
    state.pagination.limit = Number.parseInt(params.get("limit") || "20", 10) || 20;
  };

  const updatePagination = (state) => {
    const container = state.pagination.container;
    if (!container) {
      return;
    }

    if (state.pagination.total <= 0) {
      container.innerHTML = "";
      return;
    }

    const totalPages = Math.max(1, Math.ceil(state.pagination.total / state.pagination.limit));
    const currentPage = Math.min(totalPages, Math.floor(state.pagination.offset / state.pagination.limit) + 1);
    const start = Math.min(state.pagination.total, state.pagination.offset + 1);
    const end = Math.min(state.pagination.offset + state.pagination.limit, state.pagination.total);
    const atFirstPage = state.pagination.offset <= 0;
    const atLastPage = state.pagination.offset + state.pagination.limit >= state.pagination.total;

    container.innerHTML = `
      <div class="neruds-pagination" role="navigation" aria-label="Paginacao de datasets">
        <p class="neruds-pagination__info">
          Mostrando <strong>${start}-${end}</strong> de <strong>${state.pagination.total}</strong>
        </p>

        <div class="neruds-pagination__controls">
          <button class="neruds-pagination__btn" data-pagination-action="first" ${atFirstPage ? "disabled" : ""}>Primeira</button>
          <button class="neruds-pagination__btn" data-pagination-action="prev" ${atFirstPage ? "disabled" : ""}>Anterior</button>
          <span class="neruds-pagination__page">Pagina <strong>${currentPage}</strong> de <strong>${totalPages}</strong></span>
          <button class="neruds-pagination__btn" data-pagination-action="next" ${atLastPage ? "disabled" : ""}>Proxima</button>
          <button class="neruds-pagination__btn" data-pagination-action="last" ${atLastPage ? "disabled" : ""}>Ultima</button>
        </div>

        <label for="pagination-limit" class="neruds-pagination__limit-label">
          Itens por pagina:
          <select id="pagination-limit">
            <option value="10" ${state.pagination.limit === 10 ? "selected" : ""}>10</option>
            <option value="20" ${state.pagination.limit === 20 ? "selected" : ""}>20</option>
            <option value="50" ${state.pagination.limit === 50 ? "selected" : ""}>50</option>
          </select>
        </label>
      </div>
    `;

    container.querySelectorAll("[data-pagination-action]").forEach((button) => {
      button.addEventListener("click", () => {
        const action = button.getAttribute("data-pagination-action");
        if (action === "first") {
          state.pagination.offset = 0;
        }
        if (action === "prev") {
          state.pagination.offset = Math.max(0, state.pagination.offset - state.pagination.limit);
        }
        if (action === "next") {
          const nextOffset = state.pagination.offset + state.pagination.limit;
          if (nextOffset < state.pagination.total) {
            state.pagination.offset = nextOffset;
          }
        }
        if (action === "last") {
          state.pagination.offset = Math.max(0, (totalPages - 1) * state.pagination.limit);
        }
        updateHash(state);
        loadDatasets(state);
      });
    });

    const limitSelect = container.querySelector("#pagination-limit");
    if (limitSelect) {
      limitSelect.addEventListener("change", (event) => {
        const value = Number.parseInt(event.target.value, 10);
        state.pagination.limit = Number.isNaN(value) ? 20 : value;
        state.pagination.offset = 0;
        updateHash(state);
        loadDatasets(state);
      });
    }
  };

  const loadDatasets = async (state) => {
    const params = {
      q: state.filters.q,
      project: state.filters.project,
      sigilo: state.filters.sigilo,
      limit: String(state.pagination.limit),
      offset: String(state.pagination.offset),
    };

    setStatus(state, "Carregando datasets...");
    state.results.replaceChildren(renderSkeletonGrid(Math.min(6, state.pagination.limit)));

    try {
      const response = await window.fetchWithRetry(buildUrl(state.datasetsEndpoint, params), {
        headers: { Accept: "application/json" },
      });
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const data = await response.json();
      state.pagination.total = Number(data.total || 0);
      renderFacetOptions(state, data);
      renderSummary(state, data);
      renderResults(state, data);
      updatePagination(state);

      const shown = Array.isArray(data.items) ? data.items.length : 0;
      setStatus(state, `Exibindo ${shown} de ${state.pagination.total} datasets com amostras anonimizadas.`);
    } catch (error) {
      state.results.innerHTML = "";
      if (state.pagination.container) {
        state.pagination.container.innerHTML = "";
      }
      renderSummary(state, { total: 0, items: [] });
      setStatus(state, "Nao foi possivel carregar os datasets agora.");
    }
  };

  const syncFiltersFromUi = (state) => {
    state.filters.q = state.query.value.trim();
    state.filters.project = state.project.value;
    state.filters.sigilo = state.sigilo.value;
  };

  const resetFilters = (state) => {
    state.filters = { q: "", project: "", sigilo: "" };
    state.query.value = "";
    state.project.value = "";
    state.sigilo.value = "";
    state.pagination.offset = 0;
    updateHash(state);
    loadDatasets(state);
  };

  Drupal.behaviors.nerudsDatasetDiscovery = {
    attach(context) {
      once("neruds-dataset-discovery", "[data-neruds-dataset-discovery]", context).forEach((root) => {
        const settings = drupalSettings.nerudsDatasetDiscovery || {};
        const state = {
          root,
          datasetsEndpoint: settings.datasetsEndpoint || "/neruds/api/datasets",
          datasetDetailEndpoint: settings.datasetDetailEndpoint || "/neruds/api/datasets",
          samplesEndpoint: settings.samplesEndpoint || "/neruds/api/audit/samples",
          form: root.querySelector("[data-dataset-filters]"),
          query: root.querySelector("#neruds-dataset-query"),
          project: root.querySelector("#neruds-dataset-project"),
          sigilo: root.querySelector("#neruds-dataset-sigilo"),
          reset: root.querySelector("[data-dataset-reset]"),
          status: root.querySelector("[data-dataset-status]"),
          summary: root.querySelector("[data-dataset-summary]"),
          results: root.querySelector("[data-dataset-results]"),
          filters: {
            q: "",
            project: "",
            sigilo: "",
          },
          pagination: {
            container: root.querySelector("[data-dataset-pagination]"),
            offset: 0,
            limit: 20,
            total: 0,
          },
        };

        readHash(state);
        state.query.value = state.filters.q;

        state.form.addEventListener("submit", (event) => {
          event.preventDefault();
          syncFiltersFromUi(state);
          state.pagination.offset = 0;
          updateHash(state);
          loadDatasets(state);
        });

        state.project.addEventListener("change", () => {
          syncFiltersFromUi(state);
          state.pagination.offset = 0;
          updateHash(state);
          loadDatasets(state);
        });

        state.sigilo.addEventListener("change", () => {
          syncFiltersFromUi(state);
          state.pagination.offset = 0;
          updateHash(state);
          loadDatasets(state);
        });

        if (state.reset) {
          state.reset.addEventListener("click", () => resetFilters(state));
        }

        loadDatasets(state);
      });
    },
  };
})(Drupal, once, drupalSettings);
