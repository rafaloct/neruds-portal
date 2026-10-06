(function (Drupal, once) {
  Drupal.behaviors.nerudsGoogleDriveDocuments = {
    attach(context) {
      once('neruds-drive-documents', '[data-drive-documents]', context).forEach((element) => {
        const endpoint = element.dataset.driveEndpoint || '/neruds/api/drive-folder';
        const driveUrl = element.dataset.driveDocuments || '';

        if (!driveUrl) {
          return;
        }

        const folderId = extractFolderId(driveUrl);
        if (!folderId) {
          element.innerHTML = `<div class="neruds-drive-documents__error"><p>${Drupal.t('Folder URL inválida.')}</p></div>`;
          return;
        }

        loadDriveDocuments(endpoint, folderId, element);
      });
    },
  };

  function extractFolderId(url) {
    try {
      const match = url.match(/\/folders\/([a-zA-Z0-9-_]+)/);
      return match ? match[1] : null;
    } catch {
      return null;
    }
  }

  function loadDriveDocuments(endpoint, folderId, container) {
    const url = `${endpoint}?folder=${encodeURIComponent(folderId)}`;

    container.innerHTML = '<div class="neruds-drive-documents__loading"><p>' + Drupal.t('Carregando documentos...') + '</p></div>';

    fetch(url, { credentials: 'same-origin' })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
      })
      .then((data) => {
        const files = Array.isArray(data.files) ? data.files : [];
        renderDocuments(files, data.folderUrl || '', container);
      })
      .catch((error) => {
        console.error('Google Drive fetch error:', error);
        container.innerHTML = `<div class="neruds-drive-documents__error"><p>${Drupal.t('Não foi possível carregar os documentos públicos no momento.')}</p></div>`;
      });
  }

  function renderDocuments(files, folderUrl, container) {
    if (!files.length) {
      container.innerHTML = `<div class="neruds-drive-documents__empty"><p>${Drupal.t('Nenhum documento público encontrado nesta pasta.')}</p></div>`;
      return;
    }

    const documentsByType = groupByType(files);
    const html = `
      <div class="neruds-drive-documents">
        <div class="neruds-drive-documents__header">
          <p class="neruds-drive-documents__count">${Drupal.formatPlural(files.length, '1 documento', '@count documentos')}</p>
          ${folderUrl ? `<a href="${Drupal.checkPlain(folderUrl)}" class="neruds-drive-documents__folder-link" target="_blank" rel="noopener">${Drupal.t('Abrir pasta no Google Drive')}</a>` : ''}
        </div>
        <div class="neruds-drive-documents__list">
          ${renderDocumentsByType(documentsByType)}
        </div>
      </div>
    `;
    container.innerHTML = html;

    // Attach analytics tracking
    container.querySelectorAll('[data-document-url]').forEach((link) => {
      link.addEventListener('click', () => {
        if (typeof window.gtag === 'function') {
          window.gtag('event', 'neruds_document_download', {
            document_name: link.getAttribute('data-document-name'),
            document_type: link.getAttribute('data-document-type'),
          });
        }
      });
    });
  }

  function groupByType(files) {
    const groups = {
      'document': [],
      'spreadsheet': [],
      'presentation': [],
      'image': [],
      'video': [],
      'other': [],
    };

    files.forEach((file) => {
      const type = getFileType(file.mimeType);
      if (groups[type]) {
        groups[type].push(file);
      } else {
        groups.other.push(file);
      }
    });

    return groups;
  }

  function getFileType(mimeType) {
    if (!mimeType) return 'other';

    if (mimeType.includes('document') || mimeType.includes('word') || mimeType.includes('text')) {
      return 'document';
    }
    if (mimeType.includes('spreadsheet') || mimeType.includes('sheet')) {
      return 'spreadsheet';
    }
    if (mimeType.includes('presentation') || mimeType.includes('slide')) {
      return 'presentation';
    }
    if (mimeType.includes('image')) {
      return 'image';
    }
    if (mimeType.includes('video')) {
      return 'video';
    }
    return 'other';
  }

  function renderDocumentsByType(groups) {
    const typeLabels = {
      'document': Drupal.t('Documentos'),
      'spreadsheet': Drupal.t('Planilhas'),
      'presentation': Drupal.t('Apresentações'),
      'image': Drupal.t('Imagens'),
      'video': Drupal.t('Vídeos'),
      'other': Drupal.t('Outros'),
    };

    const typeIcons = {
      'document': '📄',
      'spreadsheet': '📊',
      'presentation': '🎯',
      'image': '🖼️',
      'video': '🎬',
      'other': '📎',
    };

    return Object.entries(groups)
      .filter(([, files]) => files.length > 0)
      .map(([type, files]) => {
        const items = files.map((file) => {
          const name = Drupal.checkPlain(file.name || Drupal.t('Documento'));
          const href = Drupal.checkPlain(file.url || '#');
          const modified = file.modifiedTime ? formatDate(new Date(file.modifiedTime)) : '';
          return `
            <li class="neruds-drive-documents__item">
              <a href="${href}" target="_blank" rel="noopener" class="neruds-drive-documents__link" data-document-url="${href}" data-document-name="${name}" data-document-type="${type}">
                <span class="neruds-drive-documents__item-name">${name}</span>
              </a>
              ${modified ? `<span class="neruds-drive-documents__item-date">${modified}</span>` : ''}
            </li>
          `;
        }).join('');

        return `
          <div class="neruds-drive-documents__section">
            <h3 class="neruds-drive-documents__section-title">${typeIcons[type]} ${typeLabels[type]}</h3>
            <ul class="neruds-drive-documents__section-list">
              ${items}
            </ul>
          </div>
        `;
      }).join('');
  }

  function formatDate(date) {
    return new Intl.DateTimeFormat('pt-BR', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    }).format(date);
  }
})(Drupal, once);
