(function (Drupal, drupalSettings, once) {
  function setText(element, text) {
    element.textContent = text || '';
    return element;
  }

  function metricTerm(label, value) {
    const wrapper = document.createElement('div');
    wrapper.append(setText(document.createElement('dt'), label));
    wrapper.append(setText(document.createElement('dd'), String(value || 0)));
    return wrapper;
  }

  function updateSidebar(sidebar, feature) {
    const counts = feature.properties.counts || {};
    sidebar.replaceChildren();
    sidebar.append(
      setText(document.createElement('h3'), feature.properties.name),
      setText(document.createElement('p'), feature.properties.theme || '')
    );

    const list = document.createElement('dl');
    list.append(
      metricTerm('Projetos', counts.projects),
      metricTerm('Pesquisadores', counts.researchers),
      metricTerm('Publicacoes', counts.publications),
      metricTerm('Eventos', counts.events)
    );
    const link = document.createElement('a');
    link.href = `/territorios/${encodeURIComponent(feature.properties.slug)}`;
    link.textContent = 'Ver conteudo relacionado';
    sidebar.append(list, link);
  }

  Drupal.behaviors.nerudsTerritoryMap = {
    attach(context) {
      once('neruds-territory-map', '[data-neruds-territory-map]', context).forEach((element) => {
        if (!window.L) {
          return;
        }

        const endpoint = drupalSettings.nerudsTerritoryMap?.endpoint || '/neruds/api/territories';
        const wrapper = element.closest('.neruds-territory-map');
        const sidebar = wrapper.querySelector('[data-neruds-territory-sidebar]');
        const keyboardList = wrapper.querySelector('[data-neruds-territory-list]');
        const map = L.map(element, { scrollWheelZoom: false }).setView([-10.4, -50.2], 5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        fetch(endpoint)
          .then((response) => response.json())
          .then((geojson) => {
            const territoryLayers = new Map();
            const layer = L.geoJSON(geojson, {
              style(feature) {
                const color = feature.properties.color || '#2C5530';
                return { color, fillColor: color, fillOpacity: 0.24, weight: 2 };
              },
              onEachFeature(feature, leafletLayer) {
                const counts = feature.properties.counts || {};
                leafletLayer.bindTooltip(`${feature.properties.name}: ${counts.projects || 0} projetos, ${counts.publications || 0} publicacoes`);
                leafletLayer.on('mouseover', () => leafletLayer.setStyle({ fillOpacity: 0.42, weight: 3 }));
                leafletLayer.on('mouseout', () => layer.resetStyle(leafletLayer));
                const selectTerritory = () => {
                  if (typeof window.gtag === 'function') {
                    window.gtag('event', 'neruds_territory_click', {
                      territory: feature.properties.slug,
                    });
                  }
                  map.fitBounds(leafletLayer.getBounds(), { padding: [24, 24] });
                  updateSidebar(sidebar, feature);
                  sidebar.focus?.();
                };
                territoryLayers.set(feature.properties.slug, { feature, leafletLayer, selectTerritory });
                leafletLayer.on('click', selectTerritory);
              },
            }).addTo(map);
            map.fitBounds(layer.getBounds(), { padding: [12, 12] });

            if (keyboardList) {
              keyboardList.replaceChildren();
              territoryLayers.forEach(({ feature, selectTerritory }) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'neruds-territory-map__territory-button';
                button.textContent = feature.properties.name;
                button.addEventListener('click', selectTerritory);
                keyboardList.append(button);
              });
            }
          });

        wrapper.querySelectorAll('[data-layer]').forEach((button) => {
          button.addEventListener('click', () => {
            const active = !button.classList.contains('is-active');
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
          });
        });
      });
    },
  };
})(Drupal, drupalSettings, once);
