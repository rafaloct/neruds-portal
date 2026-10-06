(function (Drupal, once) {
  Drupal.behaviors.nerudsResearchCommunities = {
    attach(context) {
      once('neruds-research-community', '[data-neruds-community]', context).forEach((community) => {
        const tabs = Array.from(community.querySelectorAll('[role="tab"]'));
        const panels = Array.from(community.querySelectorAll('[role="tabpanel"]'));

        const activate = (tab) => {
          tabs.forEach((item) => item.setAttribute('aria-selected', String(item === tab)));
          panels.forEach((panel) => {
            panel.hidden = panel.id !== tab.getAttribute('aria-controls');
          });
          if (typeof window.gtag === 'function') {
            window.gtag('event', 'neruds_group_tab_click', {
              tab_id: tab.dataset.tabId,
            });
          }
        };

        tabs.forEach((tab) => {
          tab.addEventListener('click', () => activate(tab));
          tab.addEventListener('keydown', (event) => {
            const index = tabs.indexOf(tab);
            const next = event.key === 'ArrowRight' ? tabs[index + 1] || tabs[0] : null;
            const previous = event.key === 'ArrowLeft' ? tabs[index - 1] || tabs[tabs.length - 1] : null;
            const target = next || previous;
            if (target) {
              event.preventDefault();
              target.focus();
              activate(target);
            }
          });
        });

        community.querySelectorAll('[data-drive-folder]').forEach((drive) => {
          const endpoint = drive.dataset.driveEndpoint || '/neruds/api/drive-folder';
          const folder = drive.dataset.driveFolder || '';
          if (!folder || drive.dataset.loaded === 'true') {
            return;
          }
          drive.dataset.loaded = 'true';
          const url = `${endpoint}?folder=${encodeURIComponent(folder)}`;
          fetch(url, { credentials: 'same-origin' })
            .then((response) => response.ok ? response.json() : Promise.reject(response))
            .then((data) => {
              const files = Array.isArray(data.files) ? data.files : [];
              if (!files.length) {
                drive.innerHTML = `<p>${Drupal.t('Nenhum documento público encontrado na pasta vinculada.')}</p>`;
                return;
              }
              const items = files.map((file) => {
                const name = Drupal.checkPlain(file.name || Drupal.t('Documento'));
                const href = Drupal.checkPlain(file.url || data.folderUrl || '#');
                const modified = file.modifiedTime ? `<span>${Drupal.checkPlain(new Date(file.modifiedTime).toLocaleDateString('pt-BR'))}</span>` : '';
                return `<li><a href="${href}" target="_blank" rel="noopener">${name}</a>${modified}</li>`;
              }).join('');
              drive.innerHTML = `<ul class="neruds-community__drive-list">${items}</ul>`;
            })
            .catch(() => {
              drive.innerHTML = `<p>${Drupal.t('Não foi possível carregar a lista pública do Google Drive agora.')}</p>`;
            });
        });
      });
    },
  };
})(Drupal, once);
